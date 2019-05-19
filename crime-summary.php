<?php
if (!defined('ABSPATH')) {
	exit;
}

$username = "zxl101";
$password = "S079z079";
$host = "mytestdb.cjkcq2pcruvk.us-east-2.rds.amazonaws.com";
$database="iter2";
//connect with database
$connect = mysqli_connect( $host, $username, $password, $database );
//getting data
$myquery2b = "SELECT city.city_name, crime.city_id, year(year) as crime_year, inc_per_100000_ppl FROM crime
                join city on crime.city_id = city.city_id where year(year) in (2012,2013,2014,2015,2016,2017) order by city_name;";

$query2b = mysqli_query($connect, $myquery2b);

$crime    = array();
while ( $row = mysqli_fetch_assoc( $query2b ) ) {
	$element = array();
	$element['year'] = $row['crime_year'];
	$element['city_id'] = $row['city_id'];
	$element['city_name'] = $row['city_name'];
	$element['crime_per'] = $row['inc_per_100000_ppl'];
	$crime[] = $element;
}


?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
	<meta charset="utf-8">
<!--    Reference the required js and CSS-->
	<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
	<link rel="stylesheet" href="css/ccscity_style.css">
	<script type="text/javascript" src="js/ccscity_script.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css">

	<script src="http://d3js.org/d3.v3.min.js"></script>
	<script src="http://labratrevenge.com/d3-tip/javascripts/d3.tip.v0.6.3.js"></script>
	<script src="https://d3js.org/d3-axis.v1.min.js"></script>
</head>

<body>
<!--title of the chart-->
<div class="diagram_title" >
    <span>Select Year </span>
</div>
<!--drop list for select the type of chart-->
<div id="drop_cs" style="text-align: center"></div>
<!--for diagram-->
<div class="svg2cs" style="text-align: center">
</div>
<script>
    // load the data from php
    var cdata = <?php echo json_encode($crime); ?>;

    // modify the data into appropriate structure
    var mdata = [];
    var k = 0;
    // for each row in cdata
    for (i = 0; i < cdata.length; i++) {
        // every 6 rows include the number of crimes in the same city over 6 years
        if (i % 6 == 0){
            var added = [];
            added["city_id"] = cdata[i]["city_id"];
            added["city_name"] = cdata[i]["city_name"];
            added[cdata[i]["year"]] = cdata[i]["crime_per"];
        } else if (i % 6 == 5){
            added[cdata[i]["year"]] = cdata[i]["crime_per"];
            // add the record to mdata for further usage
            mdata[k] = added;
            k = k + 1;
        } else {
            added[cdata[i]["year"]] = cdata[i]["crime_per"];
        }
    }

    // the margin, width, and height of the svg
    var margin1 = {top: 10, right: 50, bottom: 55, left: 80},
        width1 = 640 - margin1.left - margin1.right,
        height1 = 350 - margin1.top - margin1.bottom;

    // create an svg in html
    var svg1 = d3.select(".svg2cs").append("svg")
        .attr("width", width1 + margin1.left + margin1.right)
        .attr("height", height1 + margin1.top + margin1.bottom)
        .append("g")
        .attr("transform", "translate(" + margin1.left + "," + margin1.top + ")");

    // get column names
    var elements1 = Object.keys(mdata[0])
        .filter(function (d) {
            return ((d != "city_name") & (d != "city_id"));
        });

    // the first element in elements1 is the default value
    elements1=elements1.reverse();
    var selection1 = elements1[0];

		mdata.sort(function(a, b) {
							  return d3.descending(Number(a[selection1]), Number(b[selection1]))
							})
		console.log(mdata)
    // determine the domain of the y axis
    var y1 = d3.scale.linear()
        .domain([0,d3.max(mdata, function (d) {
            return +d[selection1];
        })])
        .range([height1, 0]);
    // determine the domian of the x axis
    var x1 = d3.scale.ordinal()
        .domain(mdata.map(function (d) {
            return d.city_name;
        }))
        .rangeBands([0, width1]);
    // function to create the x axis
    var xAxis1 = d3.svg.axis()
        .scale(x1)
        .orient("bottom");
    //function to create the y axis
    var yAxis1 = d3.svg.axis()
        .scale(y1)
        .orient("left");
    // draw on svg, add x axis
    svg1.append("g")
        .attr("class", "x1 axis")
        .attr("transform", "translate(0," + height1 + ")")
        .call(xAxis1)
        .selectAll("text")
        .style("font-size", "10px")
        .style("text-anchor", "end")
        .attr("dx", "1em")
        .attr("dy", "0.75em")
        .attr("transform", "rotate(-30)");
    // add y axis to the svg
    svg1.append("g")
        .attr("class", "y1 axis")
        .call(yAxis1)
        .append("text")
        .attr("transform", "rotate(-90)")
        .attr("x", -20)
        .attr("y", -55)
        .attr("dy", ".71em")
        .style("text-anchor", "end")
        .style("font-size", "12px")
        .text("Number of Crimes Per 100,000 People")
    // draw the bar charts
    svg1.selectAll("rectangle1")
        .data(mdata)
        .enter()
        .append("rect")
        .attr("class", "rectangle1")
        .attr("width", width1 / mdata.length - 20)
        .attr("height", function (d) {
            return height1 - y1(+d[selection1]);
        })
        .attr("x", function (d, i) {
            return (width1 / mdata.length) * i + 10;
        })
        .attr("y", function (d) {
            return y1(+d[selection1]);
        })
        // the bar of Mlebourne Metro will be dark red, others would be blue
        .attr("fill",function (d) {
            if (d.city_name == "Melbourne Metro"){
                return "#E2303C";
            } else{
                return "#99CCFF";}}
        )
        .style("margin-top", "10px")
        .append("title")
        .text(function (d) {
            // return the city name and number of crimes per 100000 people in the tooltip
            return d.city_name + " : " + Math.floor(d[selection1] * 100) / 100 + " cases";
        });

    // add title
    svg1.append("text")
        .attr("x", (width1 / 2))
        .attr("y", 0 - (margin1.top / 2))
        .attr("text-anchor", "middle")
        .style("font-size", "25px")
        .style("text-decoration", "underline")

    // the selector (drop-down button)
    var selector1 = d3.select("#drop_cs")
        .append("select")
        .attr("id", "dropdown1")
        // how to update the plot
        .on("change", function (d) {

            selection1 = document.getElementById("dropdown1");

						mdata.sort(function(a, b) {
											  return d3.descending(Number(a[selection1]), Number(b[selection1]))
											})

						x1 = d3.scale.ordinal()
				        .domain(mdata.map(function (d) {
				            return d.city_name;
				        }))
				        .rangeBands([0, width1]);
            // change the domain of the y axis
            y1.domain([0, d3.max(mdata, function (d) {
                return +d[selection1.value];
            })]);

            yAxis1.scale(y1);

            // change the height of the bar charts
            d3.selectAll(".rectangle1")
                .transition()
                .attr("height", function (d) {
                    return height1 - y1(+d[selection1.value]);
                })
                .attr("x", function (d, i) {
                    return (width1 / mdata.length) * (i % 11) +10;
                })
                .attr("y", function (d) {
                    return y1(+d[selection1.value]);
                })
                .ease("linear")
                .select("title")
                .text(function (d) {
                    return d.city_name + " : " + Math.floor(d[selection1.value] * 100) / 100 + " cases" ;
                });

            // change the y axis
            d3.selectAll("g.y1.axis")
                .transition()
                .call(yAxis1);

        });

    // add values to the drop down button
    selector1.selectAll("option")
        .data(elements1)
        .enter().append("option")
        .attr("value", function (d) {
            return d;
        })
        .text(function (d) {
            return d;
        });


</script>
</body>
