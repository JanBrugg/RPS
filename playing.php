<?php
$game_id = $_GET["game_id"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Game: <?php echo $game_id;?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php
$container = "container";
if (is_dir("current_games/" . $game_id)) {
    $container .= " active";
    $file = fopen("current_games/" . $game_id . "/status.txt", "w");
    $txt = "entered";
    fwrite($file, $txt);
    fclose($file);
} else {
    mkdir("current_games/" . $game_id);
    $file = fopen("current_games/" . $game_id . "/status.txt", "w");
    $txt = "alone";
    fwrite($file, $txt);
    fclose($file);
}
?>

<script>

    function add_class() {
        document.getElementById("images").classList.toggle("active")
        if (document.getElementById("btn").innerText == "show") {
            document.getElementById("btn").innerText = "hide";
        } else {
            document.getElementById("btn").innerText = "show";
        }
    }

    async function fetchResource(pathToResource) {
        try {
            const response = await fetch(pathToResource);
            if (!response.ok) {
                throw Error(`${response.status} ${response.statusText}`);
            }
            return response;
        } catch (error) {
            console.log('Looks like there was a problem: ', error);
        }
    }

    const timer = ms => new Promise(res => setTimeout(res, ms))

    async function check_update() {
        let updated = false
        let i = 1;
        while (!updated) {
            let response = await fetchResource('current_games/<?php echo $game_id;?>/status.txt');
            if (response) {
                let text = await response.text();
                console.log(text);
                if (text == "entered") {
                    add_class()
                    updated = true;
                }
            }
            await timer(500)
        }
    }

    check_update()

</script>

<div class="<?php echo container;?>" id="images">
    <img class="RPS" src="images/rock.jpg">
    <img class="RPS" src="images/paper.jpg">
    <img class="RPS" src="images/scissors.png">
</div>

</body>
</html>