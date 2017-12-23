<!-- Author : Abhinaya Ramachandran-->


<?php
	if (!isset($_SESSION)) { session_start(); }
?>

<html>
<head><title>Buy Products</title>
<!-- Scripts below are for bootstrap styles and decoration -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
<h3><span class="glyphicon glyphicon-shopping-cart"></span>
Shopping Basket:
</h3>


<?php
$totalVal =0;
$_SESSION['totalVal'] =$totalVal ;

# Clearing out the shopping cart
if(isset ($_GET['clear']) && $_GET['clear'] ==1 ){
    echo "<center>Your shopping cart is empty!</center>";
    session_unset();
}

# Deleting an entry from the shopping cart
if (isset($_GET['delete'])) {
    $key =$_GET['delete'];
    unset($_SESSION['cart'][$key]);
    $totalVal = $_SESSION['totalVal'];
    $totalVal -= $_SESSION['all'][$key]["price"];

}


# Inserting an element into the cart
if(isset($_GET['buy'])){
	$buyItemId = $_GET['buy'];
	foreach($_SESSION['all'] as $key => $value){
		if  ($key == "s". $buyItemId ){
			$_SESSION['cart'][$key] = $value;
		}
	}
	
}


#Printing out the cart
if (isset($_SESSION['cart'])){
	print "<table border='1' class='table table-border table-hover' style='border: 1px'>";
	foreach ($_SESSION['cart'] as $key => $value) {
		print "<tr class ='success'>";
		print "<td>".$value["name"]."</td>";
		print  "<td>".$value["price"]."</td>";
		print  "<td><img src='".$value["image"]."'/></td>";
		print "<td> <a href='buy.php?delete=".$key."'>Delete </a></td>";
		print "</tr>";
		$totalVal = $_SESSION['totalVal'] ;
		$totalVal += (float)$value["price"];
		$_SESSION['totalVal'] = $totalVal;
	}
	print "</table>";
print "<h4>Total Amount:  $ " .$_SESSION['totalVal'] . "</h4>";
}
?>



<form action="buy.php" method="GET">
<input type="hidden" name="clear" value="1"/>
<input type="submit" class = "btn btn-danger" value="Empty Basket"/>
</form>
<form  action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method ="GET">



<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
# Populating the dropdown box
# Put your API key in the line below
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=<PUT YOUR API KEY HERE>
	&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);
header('Content-Type: text/html');
print "<h1>".$xml."</h1>";
$elementString = "<fieldset><legend><span class='glyphicon glyphicon-search' ></span> Find Products :</legend><label>Category: </label> <select name='categories'>";
foreach($xml->category as $group){
	$elementString .= "<optgroup label=' ".htmlspecialchars($group->name) ."'>";
	$elementString .= "<option value=". $group['id'].">".htmlspecialchars($group->name)."</option>";
	foreach($group ->categories as $subcategory){
		foreach($subcategory->category as $item){
			$elementString .= "<optgroup label='".htmlspecialchars($item->name)."'>";
			$elementString .= "<option value=". $item['id'].">".htmlspecialchars($item->name)."</option>";
			foreach($item->categories as $cat){
				foreach($cat->category as $cc)
					$elementString .="<option value=". $cc['id'].">". htmlspecialchars($cc->name) ."</option>";
			}
			$elementString .="</optgroup>";
		}

	}
	$elementString .= "</optgroup>";
}
$elementString .="</select> </label>";
$elementString.= "<label>Search keywords: </label><input type='text'  name='search'/>  <input type ='submit' name='submit' class='btn btn-info' value= 'Submit'/></fieldset>";
print $elementString;
print "</form>";
$category ="";
if($_SERVER["REQUEST_METHOD"] == "GET"  && isset($_GET["search"])){
	$category = $_GET["categories"];
	$search=urlencode($_GET["search"]);
    $res     = file_get_contents('http://sandbox.api.shopping.com/publisher/3.0/rest/GeneralSearch?apiKey=<PUT YOUR API KEY HERE>
    	&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId='.$category.'&keyword='.$search.'&numItems=20');
    $results  = new SimpleXMLElement($res);
print "<table border='1' class='table table-bordered ' class='active' ><th>Product Image</th><th>Name</th><th>Description</th><th>Price in USD</th><th>Offer URL</th>";

#Printing the search results
foreach($results->categories as $cat){
	foreach($cat->category as $category){
	foreach($category->items as $subcategory){
	foreach($subcategory->children() as $item){	
	$a = $item->getName();
	if($a == "product"){
		print "<tr class='info'><td><a id =".$item["id"]." href='buy.php?buy=".$item["id"]."'><img src='".$item->images->image->sourceURL."' /></a></td>"."<td>".$item->name."</td>"."<td>".$item->fullDescription."</td>"."<td>".$item->minPrice."</td><td><a href='".(string)$item->productOffersURL."'>Click for offers</a></td></tr>";
		$items_arr= array("image"=>(string)$item->images->image->sourceURL, "name"=> (string)$item->name, 'description'=>(string)$item->fullDescription, 'price' =>(string)$item->minPrice);
		$_SESSION['all']["s".(string)$item["id"]]  = $items_arr;
		}
	else if($a == "offer"){
		print "<tr class='info'><td><a id =".$item["id"]." href='buy.php?buy=".$item["id"]."'><img src='".$item->imageList->image->sourceURL."' /></a></td>"."<td>".$item->name."</td>"."<td>".$item->description."</td>"."<td>".$item->basePrice."</td><td>".$item->productOffersURL."</td></tr>";
		$_SESSION['all']["s".(string)$item["id"]] =  array("image"=>(string)$item->imageList->image->sourceURL, "name"=>(string) $item->name, 'description'=>(string)$item->description, 'price' =>(string)$item->basePrice);
	}

	}
	}
}
}
print "</table>";
}
?>
</body>
</html>
