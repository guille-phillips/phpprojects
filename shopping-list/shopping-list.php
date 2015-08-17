<!DOCTYPE html>
<html>
        <head>
            <style>
<?php
//  http://paletton.com/#uid=13+0u0kckwv3KSE7LIcherhm5ml

	$colour=array();
	$colour[] = '#7979B4';
	$colour[] = '#D4D4EB';
	$colour[] = '#A7A7D2';
	$colour[] = '#535398';
	$colour[] = '#33337D';
?>

                body {
                    font-family:arial;
                    font-size:70px;
                    font-weight:bold;
                    background-color:<?=$colour[4]?>;
		                color:<?=$colour[4]?>;
                }

                .item {
                    position:relative;
                    width:calc:100%;
                    height:150px;
                    padding-top:7px;
                    margin-bottom:5px;
                    cursor:pointer;
                    background-color:<?=$colour[0]?>;
					color:<?=$colour[1];?>;
                }
                .quantity {
                    width:180px;
                    display:inline-block;
                    height:117px;
                    text-align:center;
                    vertical-align:top;
                    border-right:4px solid <?=$colour[0];?>;
                    padding-top:26px;
                }
                .name {
                    width:calc(100% - 330px);
                    display:inline-block;
                    vertical-align:top;
                    padding-top:26px;
					padding-left:24px;
					height:114px;
                }
                .purchase {
                    position:absolute;
                    right:0px;
                    width:180px;
                    height:125px;
                    display:inline-block;
                    text-align:center;
                    vertical-align:top;
                    border-left:4px solid <?=$colour[0];?>;
                    padding-top:18px;
                }

                .in_list {
                    background-color:<?=$colour[1];?>;
					color:<?=$colour[4];?>;
                }
                .purchased {
                    background-color:<?=$colour[3];?>;
					color:<?=$colour[1];?>
                }

				#container {
					overflow-y:scroll;
				}
				.menu {
					margin-top:27px;
					margin-bottom:27px;
                    width:100%;
                    height:140px;
                    padding-top:7px;
					position:relative;
					background-color:<?=$colour[1];?>;
				}
                .switcher {
                    width:180px;
                    height:104px;
                    cursor:pointer;
                    padding:25px 0px 5px 0px;
                    display:inline-block;
					vertical-align:top;
					background: <?=$colour[1];?> url("list.png") no-repeat center center;
                    border-right:4px solid <?=$colour[0];?>;
                }
                .create_new {
                    position:relative;
                    width:calc(100% - 306px);
                    height:126px;
                    padding-top:7px;
                    cursor:pointer;
                    background-color:<?=$colour[1]?>;
					display:inline-block;
					vertical-align:top;
                }
				input {
                    font-family:arial;
                    font-size:70px;
                    font-weight:bold;
					width:calc(100% - 120px);
					height:96px;
					display:inline-block;
					margin-left:20px;
					margin-top:9px;
					color:<?=$colour[4];?>;
					background-color:<?=$colour[1];?>;
					padding-left:14px;
					border:1px dotted <?=$colour[4];?>;
				}
				.adder {
                    position:absolute;
                    right:0px;
                    width:180px;
                    height:131px;
                    display:inline-block;
                    text-align:center;
                    vertical-align:top;
                    border-left:4px solid <?=$colour[0];?>;
					background-color:<?=$colour[1]?>;
                    padding-top:2px;
					font-size:110px;
				}
            </style>
            <?php
                date_default_timezone_set('Europe/London');

                $today = date('Y-m-d');
                //$today = '2015-08-17';
            ?>
            <script>
                timer=0;
                var xmlhttp;
                if (window.XMLHttpRequest) {
                    xmlhttp=new XMLHttpRequest();
                } else {
                    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                }

                function ajax(id,method,value) {
                    xmlhttp.open("GET","update-list.php?method="+method+"&id="+id+"&value="+value+"&date=<?php echo $today;?>",false);
                    xmlhttp.send();
					return xmlhttp.responseText;
                }

				function change_quantity(id,direction) {
					var item = document.getElementById('item_'+id);
					if (item.dataset.purchased==1) return;
					var quantity = item.dataset.quantity===''?0:parseInt(item.dataset.quantity,10);

					if (quantity==0) {
						set_item_state(item, 2);
						set_quantity(item,1);
						set_purchased(item,'');
						ajax(id,'add');
					} else if (quantity==1) {
						if (direction==-1) {
							set_item_state(item, 1);
							set_quantity(item,'');
							set_purchased(item,'X');
							//ajax(id,'remove');
							ajax(id,'quantity',0)
						} else {
							set_quantity(item,2);
							ajax(id,'quantity',2);
						}
					} else {
						set_quantity(item,quantity+direction);
						ajax(id,'quantity',quantity+direction);
					}
				}

				function set_item_state(item,state) {
					switch (state) {
						case 1: // in stock
							item.className = 'item';
							break;
						case 2: // in list
							item.className = 'item in_list';
							break;
						case 3: // purchased
							item.className = 'item purchased';
							break;
					}
				}

				function set_quantity(item,value) {
					document.getElementById('quantity_'+item.dataset.id).innerHTML = value;
					item.dataset.quantity = value;
				}
				function set_purchased(item,value) {
					document.getElementById('purchased_'+item.dataset.id).innerHTML = value;
				}
				function toggle_purchased(id) {
					var item = document.getElementById('item_'+id);
					if (item.dataset.quantity==='') {
						if (confirm('Remove stock. Are you sure?')) {
							ajax(id,'remove-stock');
							item.parentElement.removeChild(item);
							return;
						}
					} else {
						item.dataset.purchased = 1-parseInt(item.dataset.purchased,10);
						set_item_state(item,parseInt(item.dataset.purchased,10)+2);
						document.getElementById('purchased_'+id).innerHTML = item.dataset.purchased=='1'?'&#x2714;':'';
						ajax(id,'purchased',item.dataset.purchased);
					}
				}

				function add_new_stock() {
					input_box = document.getElementById('new_item');
					var response=ajax(0,'new_item',input_box.value).split('|');
					var id = response[0];
					if (id>0) {
						ajax(id,'add');
						var name = response[1];
						var div = document.createElement('div');
						div.id = 'item_' + response[0];
						div.className = 'item in_list';
						div.dataset.id = response[0];
						div.dataset.quantity = 1;
						div.dataset.purchased = 0;
						div.innerHTML = "<div id='quantity_"+id+"' class='quantity' onclick='change_quantity("+id+",-1);'>1</div><div id='name_"+id+"' class='name' onclick='change_quantity("+id+",1);'>"+name+"</div><div id='purchased_"+id+"' class='purchase' onclick='toggle_purchased("+id+");'></div></div>";

						var container = document.getElementById('container');
						var added = false;
						for (node_index in container.childNodes) {
							if (container.childNodes[node_index].innerHTML) {
								if (name<container.childNodes[node_index].childNodes[2].innerHTML) {
									container.insertBefore(div, container.childNodes[node_index]);
									added = true;
									break;
								}
							}
						}
						if (!added) {
							container.appendChild(div);
						}

						input_box.value = '';
						location.href = "#item_"+id;
					} else {
						alert(response[1]);
					}
				}

                function switch_list() {
                    var list = document.getElementById('container');
					var show_stock = list.dataset.switch == 'stock';
                    for (var node_id in list.childNodes) {
                        var node = list.childNodes[node_id];
                        if (node.innerHTML) {
                            if (show_stock) {
                                node.style.display='';
                            } else if (node.className=='item') {
                                node.style.display='none';
                            }
                        }
                    }
                    //document.getElementById('switcher').innerHTML = show_stock?'Stock':'Shop';
					document.getElementById('switcher').style.backgroundImage = show_stock?"url('list.png')":"url('stock.png')";
                    list.dataset.switch = show_stock?'shopping':'stock';
                }

				function set_list_height() {
					var height=window.innerHeight;
					document.getElementById('container').style.height=height-180+'px';
				}
            </script>
        </head>
        <body onload="set_list_height();" onresize="set_list_height();">
<?php
        $db = new mysqli('localhost', 'root', 'almeria72', 'shopping_list');
        //$db = new mysqli('localhost', 'root', '', 'shopping_list');

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        // Recommendations
        //// Remove unpurchased > 2 weeks
        //// Work out average usage per day for each item

		$sql = <<<SQL
			DELETE
			FROM
				shopping_list
			WHERE
				quantity = 0
				AND item_date < '$today'
SQL;
		if (!$db->query($sql)) {
			die('There was an error running the query [' . $db->error . ']');
		}

        $sql = <<<SQL
            SELECT
                s.stock_id,
                MAX(s.quantity) as maximum_purchased
            FROM
                shopping_list s
            WHERE
                s.purchased = 1
            GROUP BY
                s.stock_id
SQL;

        if(!$list = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }

        $max = array();
        while($row = $list->fetch_assoc()){
            $max[$row['stock_id']]=$row['maximum_purchased'];
        }

        $sql = <<<SQL
            SELECT
                s.stock_id,
                s.quantity,
                s.item_date
            FROM
                shopping_list s
            WHERE
                s.purchased = 1
                AND s.item_date < '$today'
            ORDER BY
                s.stock_id,
                s.item_date DESC
SQL;

        if(!$list = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }

        $recommendations = array();

        $previous_stock_id = -1;
        $previous_item_date = '2000-01-01';
        $quantity_sum = 0;
        $day_sum = 0;
        $most_recent_date = '';
        $most_recent_quantity = 0;

        $rows = array();
        while ($row = $list->fetch_assoc()) {
            $rows[] = $row;
        }

        $rows[] = array('stock_id'=>-1,'quantity'=>0,'item_date'=>'');

        foreach ($rows as $row) {
            foreach ($row as $field=>$value) {
                $$field = $value;
            }

            if ($stock_id==$previous_stock_id) {
                $start = new DateTime($item_date);
                $end  = new DateTime($previous_item_date);
                $diff = $start->diff($end);
                $days = $diff->days;
                $day_sum += $days;
                $quantity_sum += $quantity;
            } else {
                if ($previous_stock_id!=-1 && $day_sum>0) {
                    $average_daily = $quantity_sum/$day_sum;

                    $start = new DateTime($today);
                    $end  = new DateTime($most_recent_date);
                    $diff = $start->diff($end);
                    $days = 7+$diff->days;
                    $used = $most_recent_quantity-$days*$average_daily;
                    if ($used<=0) {
                        $recommended_quantity = intval(abs($used))+1;
                        if ($recommended_quantity<=(3*$max[(int)$previous_stock_id])) {
                            $recommendations[(int)$previous_stock_id] = $recommended_quantity;
                        }
                    }
                }

                $most_recent_date = $item_date;
                $most_recent_quantity = $quantity;
                $quantity_sum = 0;
                $day_sum = 0;
            }
            $previous_stock_id = $stock_id;
            $previous_item_date = $item_date;
            $previous_quantity = $quantity;
        }

        if(!$list = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }
//var_dump($recommendations);
        $rows = array();
        while ($row = $list->fetch_assoc()) {
            $rows[] = $row;
        }

        // Get list
        $sql = <<<SQL
            SELECT
                k.stock_id,
                k.name,
                s.quantity,
                s.purchased,
				s.dirty,
				s.item_date
            FROM
                stock k
                LEFT JOIN
                `shopping_list` s
                ON s.stock_id=k.stock_id AND ((s.purchased=1 AND s.item_date = '$today') OR (s.purchased=0))
            ORDER BY
                k.name
SQL;

        if(!$list = $db->query($sql)){
            die('There was an error running the query [' . $db->error . ']');
        }
//var_dump($recommendations);
        $rows = array();
        while ($row = $list->fetch_assoc()) {
            $rows[] = $row;
        }

        foreach ($rows as &$row) {
            foreach ($row as $field=>$value) {
                $$field = $value;
            }

            if (isset($recommendations[$stock_id])) {
                if ($purchased!==null && $purchased==0 && ($dirty==0 || ($dirty==1 && $item_date!=$today))) {
                    $sql = "UPDATE `shopping_list` SET quantity={$recommendations[$stock_id]} WHERE stock_id = $stock_id AND purchased=0";
                    $row['quantity']=$recommendations[$stock_id];
                    $db->query($sql);
                    unset($recommendations[$stock_id]);
                }
            }
        }
        unset($row);

        foreach ($rows as &$row) {
            foreach ($row as $field=>$value) {
                $$field = $value;
            }

            if (isset($recommendations[$stock_id])) {
                if ($purchased==null) {
                    $sql = "INSERT into shopping_list (stock_id,quantity,item_date) VALUES ($stock_id,{$recommendations[$stock_id]},'$today')";
                    if (!$db->query($sql)) {
                        die('There was an error running the query [' . $db->error . ']');
                    }
                    $row['quantity']=$recommendations[$stock_id];
                }
            }
        }
        unset($row);
		echo "<div class='menu'>";
        echo "<div class='switcher' id='switcher' data-switch='stock' onclick='switch_list();'></div>";
		echo "<div class='create_new'><input id='new_item' type='text' placeholder='Create New' onkeyupx='e = event || window.event;if (e.keyCode==13) add_new_stock();' onfocus='this.setSelectionRange(0, this.value.length);'></div>";
		echo "<div class='adder' onclick='add_new_stock()'>+</div>";
		echo "</div>";
        echo "<div id='container'>";
        foreach ($rows as $row) {
            foreach ($row as $field=>$value) {
				$data_field = 'data_'.$field;
                $$data_field = $value;
            }

            $id = $data_stock_id;
			$data_quantity_null_or_zero = ($data_quantity==NULL) || ($data_quantity==0);
            $class = $data_purchased==1?' purchased':(!$data_quantity_null_or_zero?' in_list':'');
            $quantity = $data_quantity_null_or_zero?'':$data_quantity;
            $purchased = $data_purchased==1?'&#x2714;':($data_quantity_null_or_zero?'X':'');
			$data_purchased = $data_purchased==NULL?0:$data_purchased;

            echo <<<HTML
                <div id='item_$id' class='item$class' data-id='$id' data-quantity='$quantity' data-purchased='$data_purchased'>
                    <div id='quantity_$id' class='quantity' onclick='change_quantity($id,-1);'>
                        $quantity
                    </div><div id='name_$id' class='name' onclick='change_quantity($id,1);'>$data_name
                    </div><div id='purchased_$id' class='purchase' onclick='toggle_purchased($id);'>
                        $purchased
                    </div>
                </div>
HTML;
        }
        echo "</div>";



?>
        </body>
</html>
