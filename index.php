<?php

require 'db.php';

echo $db->execute_query("SELECT * FROM post")->fetch_all()[0]["judul"];