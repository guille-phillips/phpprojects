<?php

        $db = new mysqli('localhost', 'root', 'almeria72', 'shopping_list');
        //$db = new mysqli('localhost', 'root', '', 'shopping_list');

        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }

        $today = $_GET['date'];
        //$today = '2015-08-17';
        
        switch ($_GET['method']) {
			case 'new_item':
				$name = $_GET['value'];
				$allowable = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -';
				$sanitised = '';
				for ($i=0; $i<strlen($name); $i++) {
					if (strpos($allowable,substr($name,$i,1))!==false) {
						$sanitised .= substr($name,$i,1);
					}
				}
				$sanitised = ucwords(trim($sanitised));

				if ($sanitised == '' || $sanitised == 'Create New') die ('0|Item not added');

				$sql = <<<SQL
					SELECT
						stock_id
					FROM
						stock
					WHERE
						name = '$sanitised'
SQL;
				if (!$stock=$db->query($sql)) {
                    die('0|There was an error running the query [' . $db->error . ']');
                }

				if ($stock->num_rows > 0) {
					die('0|Item already added');
				}

				$sql = <<<SQL
					INSERT INTO
						stock
						(`name`)
						VALUES
						('$sanitised')
SQL;
                if (!$db->query($sql)) {
                    die('0|There was an error running the query [' . $db->error . ']');
                }
				echo $db->insert_id.'|'.$sanitised;
				break;
            case 'quantity':
                $sql = <<<SQL
                    UPDATE
                        shopping_list
                    SET
                        quantity={$_GET['value']},
						item_date='$today',
						dirty=1
                    WHERE
                        stock_id={$_GET['id']}
                        AND purchased=0
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                break;
            case 'purchased':
                $sql = <<<SQL
                    UPDATE
                        shopping_list
                    SET
                        purchased={$_GET['value']},
						item_date = '$today',
						dirty=1
                    WHERE
                        stock_id={$_GET['id']}
						AND (purchased = 0 OR (purchased=1 AND item_date='$today'))
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                break;
            case 'remove':
                $sql = <<<SQL
                    DELETE
                    FROM
                        shopping_list
                    WHERE
                        stock_id={$_GET['id']}
                        AND (purchased=0 OR (purchased=1 AND item_date='$today'))
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                break;
            case 'add':
                $sql = <<<SQL
                    INSERT INTO
                        shopping_list
                        (stock_id, item_date)
                    VALUES
                        ({$_GET['id']}, '$today')
					ON DUPLICATE KEY UPDATE quantity=1, dirty=1
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                break;
            case 'remove-stock':
                $sql = <<<SQL
                    DELETE
                    FROM
                        stock
                    WHERE
                        stock_id={$_GET['id']}
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                $sql = <<<SQL
                    DELETE
                    FROM
                        shopping_list
                    WHERE
                        stock_id={$_GET['id']}
SQL;
                if (!$db->query($sql)) {
                    die('There was an error running the query [' . $db->error . ']');
                };

                break;
        }

?>
