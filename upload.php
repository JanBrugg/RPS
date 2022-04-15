<?php
$game_id = $_POST["game_id"];

$targetPath = "current_games/" . $game_id . "/" . basename($_FILES["inpFile"]["name"]);
move_uploaded_file($_FILES["inpFile"]["tmp_name"], $targetPath);