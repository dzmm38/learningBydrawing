<?php
session_start();
if(isset($_SESSION["idUser"])){
    include 'db_connector.php';
    $uuid = $_SESSION["uuid"];

    $allCategorys = [];
    $allLearningplans = [];
    $allLearningplanIds = [];
    $sql = "select category as cat from datasets;";
    $stmt = mysqli_stmt_init($connection);
    if(!mysqli_stmt_prepare($stmt, $sql)){
    echo "SQL Statement failed";
    }else{
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            array_push($allCategorys,$row["cat"]);
            $allLearningplans[$row["cat"]] = [];
            $allLearningplanIds[$row["cat"]] = [];
        }
    }

    //get all Learning plans and save them into array
    $sql = "select * from learningplans where uuid=?;";
    $stmt = mysqli_stmt_init($connection);
    if(!mysqli_stmt_prepare($stmt, $sql)){
    echo "SQL Statement failed";
    }else{
        mysqli_stmt_bind_param($stmt, "s", $uuid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = $result->fetch_assoc()) {
            array_push($allLearningplans[$row["category"]],$row["name"]);
            array_push($allLearningplanIds[$row["category"]],$row["ID"]);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lernauswahl</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/background.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="externScripts/tf.min.js"></script>
    <script>

        function highliteCategory(e){
            let clickedBox = e;
            let clickedCategory = e.childNodes[0].innerHTML;
            let allBtns = document.querySelectorAll(".category");
            for(let i=0;i<allBtns.length;i++){
                if(allBtns[i].style.backgroundColor == "green"){         
                    let greenBtn = allBtns[i];
                    greenBtn.style.backgroundImage = "linear-gradient( 135deg, #43CBFF 10%, #9708CC 100%)";
                } 
            }
            e.style.backgroundImage = "none";
            e.style.backgroundColor = "green";

            document.getElementById("choosenCategory").value = clickedCategory;

            //create option elements for each learnplan
            let allLearnPlans = document.querySelectorAll("."+clickedCategory);
            //checking if there are learnplans for the category and if not disabling the checkbox + changning opacity
            if(allLearnPlans.length > 0){
                document.getElementById("learningPlanSwitch").disabled= false;   
                document.getElementById("useLearnplanWrapper").style.opacity ="1";
                document.getElementById("learnplanSlider").style.cursor ="pointer";

                //clear the currentLearn plans
                document.getElementById("lpSelectionField").innerHTML = "";

                for(let i=0;i<allLearnPlans.length;i++){
                    let opt = document.createElement('option');
                    opt.value = allLearnPlans[i].id;
                    opt.innerHTML = allLearnPlans[i].value;
                    document.getElementById("lpSelectionField").appendChild(opt);
                }
            }else{
                if(document.getElementById("learningPlanSwitch").checked){
                    document.getElementById("learningPlanSwitch").click();
                }
                document.getElementById("learningPlanSwitch").disabled= true;
                document.getElementById("useLearnplanWrapper").style.opacity ="0.4";
                document.getElementById("learnplanSlider").style.cursor ="not-allowed";
            }
        }


        function submitForm(){
            var counter = 0;
            let allBtns = document.querySelectorAll(".category");
            for(let i=0;i<allBtns.length;i++){
                if(allBtns[i].style.backgroundColor == "green"){         
                    counter++;
                } 
            }
            if(counter > 0){
                document.getElementById("startForm").submit();
            }else{
                //TODO placeholder alert, needs to be replaced by nice looking error
                alert("Bitte eine Kategorie auswählen");
            }
        }


        function backgroundRandomizer(){
            setInterval(function () {
                var rndChars = "きすつなはまやれをきすつなはまやれをabcdefghijklmnopqrstuwvxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789";
                let allRndChars = document.querySelectorAll(".rndChar");
                for(let i=0;i<allRndChars.length;i++){
                    let rndChar= rndChars[Math.floor(Math.random() * rndChars.length)];
                    allRndChars[i].innerHTML = rndChar
                }
            }, 22000);
        }

        function toggleLearningPlans(){
            let greenCount = 0;
            let allBtns = document.querySelectorAll(".category");
            for(let i=0;i<allBtns.length;i++){
                if(allBtns[i].style.backgroundColor == "green"){
                    greenCount++;       
                } 
            }
            if(greenCount == 0){
                //maybe replace with nice error message
                alert("Du musst zuerst oben eine der Kategorien auswählen!");
                $("#learningPlanSwitch").prop("checked", false);
                return;
            }
            $("#lpSelectionWrapper").fadeToggle();
            $("#repeatLabelWrap").fadeToggle();
        }


        function hideLearningPlans(){
            $("#learningplanWrapper").fadeToggle();
            $("#repeatLabelWrap").show();
            $("#learningPlanSwitch").prop("checked", false);
            $("#lpSelectionWrapper").hide();
            
        }
    </script>
</head>
<body class="area" onload="backgroundRandomizer()">
    <?php
        //loop through the learning plans and print them in hidden input to be able to access in the frontend
        for($i=0;$i<count($allCategorys);$i++){
            for($a=0;$a<count($allLearningplans[$allCategorys[$i]]);$a++){
                print "<input type='hidden' style='display:none;' id='".$allLearningplanIds[$allCategorys[$i]][$a]."' class='".$allCategorys[$i]."' value='".$allLearningplans[$allCategorys[$i]][$a]."'>";
            }
        }   
    ?>
    <form action="login_system/logout_script.php" method='post' id='logoutForm'>
        <div id="userWrapper" onclick="location.href='resultsAndPlans.php'">
            <img src="media/circle-user.svg" id="userCircle">
            <p id='loginUserHello'><?=$_SESSION["idUser"] ?></p>
        </div>
        <input type="submit" id="logout" value="Logout"></button>
    </form>
    <h1 id="headline">Lernauswahl</h1>
    <div class="content">
        <div id="categoryWrapper">
            <!--Later fetch the categorys from the database here: corresponding table is "datasets"-->
            <?php
                for($i=0;$i<count($allCategorys);$i++){
                    print '<div class="category" onclick="highliteCategory(this)">';
                    print'<p>'.$allCategorys[$i].'</p>';
                    print '</div>';
                }
            ?>
        </div>
    </div>
    <div class="content" id="content2">
        <form action="drawing.php" method="post" id="startForm">
                <div id='repeatsWrapper'>
                    <label id='repeatLabelWrap'>Anzahl der Übungen: 
                        <input type="number" step="1" id="repeats" name="repeats" min="1" value="1">
                    </label>
                </div>
                <div id='intLearningWrapper'>
                    Intelligentes Lernen: 
                    <label class="switch">
                        <input type="checkbox" name="intLearning" id='intLearningSwitch' onclick="hideLearningPlans()">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div id='learningplanWrapper'>
                    <div id="useLearnplanWrapper">
                        Lernplan verwenden: 
                        <label class="switch">
                            <input type="checkbox" name="enableLearningplan" id='learningPlanSwitch' onclick="toggleLearningPlans()">
                            <span class="slider round" id="learnplanSlider"></span>
                        </label>
                    </div>
                    <div id="lpSelectionWrapper">
                        <label id="learningPlanSelection">
                            Lernplan auswählen: 
                            <select id='lpSelectionField' name='selectedLearningplan'>
                            </select>
                        </label>
                    </div>
                </div>
                <input type="hidden" id="choosenCategory" name="category">
        </form>
    </div>
    <button id="startBtn" onclick="submitForm()">Übung Starten</button>

    <!--for the Background animation-->
    <ul class="circles">
        <li></li>
        <li></li>
        <li class="rndChar">a</li>
        <li class="rndChar">4</li>
        <li class="rndChar">F</li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li class="rndChar">K</li>
        <li class="rndChar">8</li>
        <li></li>
        <li></li>
        <li></li>
        <li class="rndChar">u</li>
        <li class="rndChar">L</li>
    </ul>
    <div id="impressumWrapper">
        <a href="impressum.html" id="impressum">Impressum</a>
    </div>
    
</body>

</html>

<?php
}else{
    header("LOCATION: index.html");
}
?>


