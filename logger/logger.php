<?php
        $db = new mysqli('localhost', 'root', 'almeria72', 'logger');

		function ExecuteQuery($sql) {
			global $db;

	        if(!$list = $db->query($sql)){
	            die('There was an error running the query [' . $db->error . ']');
	        }

	        if ($list!==true) { // check if we have results
		        $rows = array();
		        while ($row = $list->fetch_assoc()){
		            $rows[]=$row;
		        }
		        return $rows;
		    }
		}


        if($db->connect_errno > 0){
            die('Unable to connect to database [' . $db->connect_error . ']');
        }
        $prm = $_GET['prm'];

        $method = $prm[0];

        switch ($method) {
            case 'add':
            	$description = $prm[1];
            	$sql = <<<SQL
                    INSERT INTO 
                        tasks
                    (description)
                    VALUES
                    ('$description')
SQL;
				ExecuteQuery($sql);
                echo $db->insert_id;
                break;
            case 'remove':
            	$id = $prm[1];
                $sql = <<<SQL
                    DELETE FROM 
                        tasks
                    WHERE
                    	task_id = $id
SQL;
				ExecuteQuery($sql);
				break;
            case 'load':
            	$start_date = $prm[1];
            	$end_date = $prm[2];
                $sql = <<<SQL
                    SELECT  
                        t.task_id,
                        t.description,
                        s.task_id IS NULL state
                    FROM
                    	tasks t
                        LEFT JOIN 
                        times s
                        ON s.task_id = t.task_id AND s.total IS NULL
SQL;
                $rows = ExecuteQuery($sql);
                echo json_encode($rows);
                break;
            case 'start':
                $sql = <<<SQL
                    SELECT 
                    FROM
                    	tasks
                    WHERE
                    	task_id = {$prm[1]}
                    	AND
                    	total IS NULL
SQL;

				$rows = ExecuteQuery($sql);

				if ($rows===false) {
	                $sql = <<<SQL
	                    INSERT INTO  
	                    	times
	                    	(task_id,
	                    	start)
	                    VALUES
	                    	({$prm[1]},
	                    	NOW() )
SQL;
					ExecuteQuery($sql);
				}

            	break;
            case 'stop':
                $sql = <<<SQL
                    SELECT 
                    	start
                    FROM
                    	tasks
                    WHERE
                    	task_id = {$prm[1]}
                    	AND
                    	total IS NULL
SQL;

				$rows = ExecuteQuery($sql);
				if ($rows!==false && count($rows)==1) {
					$start = $rows[0]['start'];
					$total = date('u')-$start;
				}

                $sql = <<<SQL
                    UPDATE
                    	tasks
                    SET
                    	total = $total
                    WHERE
                    	task_id = {$prm[1]} 
                    	AND start = $start
SQL;
				ExecuteQuery($sql);				

            	break;
        }
