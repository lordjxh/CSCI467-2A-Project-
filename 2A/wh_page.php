<html>

<style>
table, th, td {
  border:1px solid black;
}
</style>

    <h1>TEST</h1>
<?php
    $servername = "";
    $username = "";
    $password = "";
    $dbname = "";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    $invoice = " ";

    $sql = "SELECT * FROM WarehouseDB";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
      echo "<table ><tr><th>No.</th><th>Price</th><th>Weight</th></tr>";
      // output data of each row
      while($row = $result->fetch_assoc()) {
        echo "<tr><td>".$row["invoiceNO"]."</td><td>".$row["fulfillmentStatus"]."</td><td>".$row["productID"]."</td><td>".$row["quantityShipped"]."</td><td><form method="POST"><input type = "button" name = "Quantity+" value = ".$row["invoiceNO"]." ></form></td></tr>";
      }
      echo "</table>";
    } else {
      echo "0 results";
    }
    
    if (isset($_POST['Quantity+']))
    {
        
    }

    $conn->close();
?>
</html>