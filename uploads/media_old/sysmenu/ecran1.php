<?php 
include($_SERVER['DOCUMENT_ROOT'].'/db.php');


$req_foodlist_data = $mysql->prepare("SELECT * FROM sysmenu_foodlist where category_id = 1");
$req_foodlist_data->execute();

$count['number_food'] = 0;

while ($foodlist_data = $req_foodlist_data->fetch())
{

    $foodlist[$count['number_food']]['category'] = "pizza";
    $foodlist[$count['number_food']]['food_name'] = $foodlist_data['food_name'];
    $foodlist[$count['number_food']]['food_ingredients'] = json_decode($foodlist_data['food_ingredients'], true);
    $foodlist[$count['number_food']]['price'] = json_decode($foodlist_data['price'], true);
    $count['number_food']++;
}
function aff_ingredients($list)
{
	$list_count = 0;

	while($list_count < count($list))
	{
		$text_result = $text_result.$list[$list_count];
		if ($list_count+1 != count($list))
			$text_result = $text_result." - ";
		$list_count++;
	}
	return($text_result);
}

?>
<!DOCTYPE html>
<html>
<head>
<style>
body {
    background-image: url("http://puu.sh/q5tU3/5f9528ad76.jpg");
    background-repeat:no-repeat;
    background-position:50% 50%;
}
</style>
</head>
<body>
<?php 
$count['aff_food'] = 0;
?>
<table cellpadding="0" cellspacing="0">
   <tr>
      <th style="font: italic bold 50px arial, sans-serif;">Nos <?php echo $foodlist[$count['aff_food']]['category']; ?></th>
      <th style="background-color: black; color: white; border: 1px solid white; padding: 15px; text-align: left;">JUNIOR</th>
      <th style="background-color: black; color: white; border: 1px solid white; padding: 15px; text-align: left;">SÉNIOR</th>
      <th style="background-color: black; color: white; border: 1px solid white; padding: 15px; text-align: left;">MÉGA</th>
   </tr>
<?php 
while ($count['aff_food'] < $count['number_food'])
{
	$ingredients = aff_ingredients($foodlist[$count['aff_food']]['food_ingredients']);
?>
   <tr style="background-color: rgba(0, 0, 0, 0.6); color: white;">
      <td style="padding: <?php if($count['aff_food'] == 0) { echo "5"; } else { echo "2.5"; } ?>px 3px <?php if($count['aff_food'] == $count['number_food']) { echo "5"; } else { echo "0"; } ?>px 8px;"><span style="color:red;font-weight:bold"><?php echo strtoupper($foodlist[$count['aff_food']]['food_name']); ?></span> <span style="color:gold;font-weight:bold"><?php echo $ingredients; ?></span></td>
      <td style="padding: <?php if($count['aff_food'] == 0) { echo "5"; } else { echo "0"; } ?>px 3px <?php if($count['aff_food'] == $count['number_food']) { echo "5"; } else { echo "0"; } ?>px 8px;"><?php echo $foodlist[$count['aff_food']]['price']['junior'].' €'; ?> </td>
      <td style="padding: <?php if($count['aff_food'] == 0) { echo "5"; } else { echo "0"; } ?>px 3px <?php if($count['aff_food'] == $count['number_food']) { echo "5"; } else { echo "0"; } ?>px 8px;"><?php echo $foodlist[$count['aff_food']]['price']['senior'].' €'; ?> </td>
      <td style="padding: <?php if($count['aff_food'] == 0) { echo "5"; } else { echo "0"; } ?>px 3px <?php if($count['aff_food'] == $count['number_food']) { echo "5"; } else { echo "0"; } ?>px 8px;"><?php echo $foodlist[$count['aff_food']]['price']['mega'].' €'; ?> </td>
   </tr>
<?php
$count['aff_food']++;
}
?>
</table>
</body>
</html>

