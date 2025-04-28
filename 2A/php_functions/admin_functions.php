<?php

function getShippingWeights($database)
{
    $statement = "SELECT * FROM ShippingWeights;";
    $weights = getSQL($database, $statement);
    return $weights;
}

function setShippingWeights($database)
{
    $index = 0;
    $total = $_POST['count'];

    while ($index < $total)
    {
        $id = $_POST[$index];
        $minWeight = $_POST['weightMin_' . $index];
        $maxWeight = $_POST['weightMax_' . $index];
        $percent = $_POST['percent_' . $index];

        $statement = "UPDATE ShippingWeights SET minimumWeight = " . $minWeight . ", maximumWeight = " . $maxWeight . ", shippingPercent = " .
            $percent . " WHERE weightID = " . $id . ";";

        insertDatabaseValue($database, $statement);
        $index++;
    }

    echo "<p>Weights updated successfully</p>";
}

function printShippingWeights($weights)
{
    echo "<form id=\"regForm\" method=\"post\">";
    echo "<table>";

    echo "<tr><td>Minimum $</td><td>Maximum $</td><td>Percentage</td></tr>";

    $index = 0;

    foreach($weights as $w)
    {
        $id[$index] = $w['weightID'];
        $minWeight[$index] = $w['minimumWeight'];
        $maxWeight[$index] = $w['maximumWeight'];
        $percent[$index] = $w['shippingPercent'];

        echo "<tr class=\"weightRow\">";
        echo "<input id=\"" . $index . "\" name=\"" . $index . "\" type=\"hidden\" value=\"" . $id[$index] . "\">";
        echo "<td><input id=\"weightMin_" . $index . "\" name=\"weightMin_" . $index . "\" value=\"" . $minWeight[$index] . "\"></p>";
        echo "<td><input id=\"weightMax_" . $index . "\" name=\"weightMax_" . $index . "\" value=\"" . $maxWeight[$index] . "\"></p>";
        echo "<td><input id=\"percent_" . $index . "\" name=\"percent_" . $index . "\" value=\"" . $percent[$index] . "\"></p>";
        echo "</tr>";

        $index++;
    }

    echo "</table>";
    echo "<input id=\"count\" name=\"count\" type=\"hidden\" value=\"" . $index . "\">";
    echo "<button type=\"submit\" id=\"update\" name=\"update\" class=\"button\">Update</button>";
    echo "</form>";
}
?>