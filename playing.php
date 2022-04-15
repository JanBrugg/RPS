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

<h2 id="waiting_text">
    Waiting for opponent...
</h2>

<script>
<?php
if (is_dir("current_games/" . $game_id)) {
    $file = fopen("current_games/" . $game_id . "/status.txt", "w");
    $txt = "entered";
    fwrite($file, $txt);
    fclose($file);

    $file = fopen("current_games/" . $game_id . "/p2.txt", "w");
    $txt = "nothing";
    fwrite($file, $txt);
    fclose($file);

    echo 'const player = 2; const opp = 1; const game_id = "' . $game_id . '";';

} else {
    mkdir("current_games/" . $game_id);
    $file = fopen("current_games/" . $game_id . "/status.txt", "w");
    $txt = "alone";
    fwrite($file, $txt);
    fclose($file);

    $file = fopen("current_games/" . $game_id . "/p1.txt", "w");
    $txt = "nothing";
    fwrite($file, $txt);
    fclose($file);

    echo 'const player = 1; const opp = 2; const game_id = "' . $game_id . '";';

}
?>


    function add_class() {
        document.getElementById("images").classList.toggle("active");
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

    const timer = ms => new Promise(res => setTimeout(res, ms));

    async function waiting_for_opponent() {
        let updated = false;
        let i = 1;
        while (!updated) {
            let response = await fetchResource(`current_games/${game_id}/status.txt`);
            if (response) {
                let text = await response.text();
                console.log(text);
                if (text === "entered") {
                    add_class();
                    updated = true;
                    document.getElementById("waiting_text").remove();
                    continue;
                }
            }
            i++;
            let text = "Waiting for opponent";
            let dots = ".";
            let amount = (i % 3) + 1;
            dots = dots.repeat(amount)
            document.getElementById("waiting_text").innerText = text + dots
            await timer(500);
        }
    }

    waiting_for_opponent()

    function update_images(selection) {
        document.getElementById("rock").classList.toggle("active");
        document.getElementById("paper").classList.toggle("active");
        document.getElementById("scissors").classList.toggle("active");
        document.getElementById(selection).classList.toggle("active");

        document.getElementById("rock").onclick = null;
        document.getElementById("paper").onclick = null;
        document.getElementById("scissors").onclick = null;

        document.getElementById("vs").classList.toggle("active");
        document.getElementById("questionmark").classList.toggle("active");
    }

    function upload_selection(selection, player, game_id) {
        let fileName = "p" + player.toString() + ".txt";
        const inpFile = new File([selection], fileName, {
            type: "text/plain",
        });


        const endpoint = "upload.php";
        const formData = new FormData();

        formData.append("inpFile", inpFile);
        formData.append("game_id", game_id)

        fetch(endpoint, {
            method: "post",
            body: formData
        }).catch(console.error);
    }

    async function selected(selection) {
        let updated = false;

        update_images(selection);

        upload_selection(selection, player, game_id);

        console.log(selection, typeof selection);
        console.log(player, typeof player);
        console.log(game_id, typeof game_id);

        // waiting for opponent to select
        while (!updated) {
            let opponent_choice = await fetchResource(`current_games/${game_id}/p${opp}.txt`);
            const opp_selection = await opponent_choice.text();
            if (opp_selection != "nothing") {
                let winner;
                if (player === 1) {
                    winner = check_winner(selection, opp_selection);
                } else {
                    winner = check_winner(opp_selection, selection);
                }
                document.getElementById("questionmark").src = "images/" + opp_selection + ".jpg";
                if (winner === 0) {
                    console.log("It's a tie.");
                    document.getElementById("result").innerText = "It's a tie.";
                } else if (winner === player) {
                    console.log("You won!");
                    document.getElementById("result").innerText = "You won!";
                } else {
                    console.log("You lost...!");
                    document.getElementById("result").innerText = "You lost...";
                }
                updated = true;
                if (player === 1) change_status();
                continue;
            }
            console.log("waiting for opponent...");
            await timer(500);
        }
    }

    function change_status() {
        let fileName = "status.txt";
        const inpFile = new File(["finished"], fileName, {
            type: "text/plain",
        });


        const endpoint = "upload.php";
        const formData = new FormData();

        formData.append("inpFile", inpFile);
        formData.append("game_id", game_id)

        fetch(endpoint, {
            method: "post",
            body: formData
        }).catch(console.error);
    }

    function check_winner(p1, p2) {

        // rock vs paper
        // paper vs scissors
        // scissors vs rock
        // tie

        if(p1 === 'rock' && p2 === 'paper') return 2;
        if (p1 === "paper" && p2 === 'rock') return 1;

        if(p1 === "scissors" && p2 === 'rock') return 2;
        if (p1 === 'rock' && p2 === 'scissors') return 1;

        if (p1 === 'paper' && p2 === 'scissors') return 2;
        if (p1 === 'scissors' && p2 === 'paper') return 1;

        // tie
        return 0
        }

</script>

<div class="container" id="images">
    <img class="RPS active" id="rock" onclick="selected('rock')" src="images/rock.jpg" alt="rock">
    <img class="RPS active" id="paper" onclick="selected('paper')" src="images/paper.jpg" alt="paper">
    <img class="RPS active" id="scissors" onclick="selected('scissors')" src="images/scissors.jpg" alt="scissors">
    <img class="RPS" id="vs" src="images/vs.jpg" alt="vs">
    <img class="RPS" id="questionmark" src="images/question_mark.jpg" alt="opponent">
</div>

<h3 id="result"></h3>

</body>
</html>