<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type:text/html;charset=utf-8');
mb_internal_encoding("utf-8");
?>
<meta charset="utf-8">
<?php

$charlist = " ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&é(-è_çà)=$1234567890?.!,;:*/\\+-'\"#{[|@]}%µ§";
$char_codes_list = mb_str_split($charlist);
$char_codes = [];

foreach ($char_codes_list as $i => $cc) {
    $char_codes[$cc] = $i;
}
function showMatrix($matrix)
{
    echo strMatrix($matrix);
}
function strMatrix($matrix){
    [[$a, $b], [$c, $d]] = $matrix;
    $stroutput= "<table border=1>";
    $stroutput.= "<tr><td>$a</td><td>$b</td></tr>";
    $stroutput.= "<tr><td>$c</td><td>$d</td></tr>";
    $stroutput.= "</table>";
    return $stroutput;
}
function showPair($pair)
{
    [$x, $y] = $pair;
    echo "<table border=1>";
    echo "<tr><td>$x</td></tr>";
    echo "<tr><td>$y</td></tr>";
    echo "</table>";
}
function showMultiply($matrix_a, $pair_b)
{
    global $char_codes_list;
    [[$a, $b], [$c, $d]] = $matrix_a;
    [$x, $y] = $pair_b;
    $char_x = $char_codes_list[$x];
    $char_y = $char_codes_list[$y];
    showMatrix($matrix_a);
    echo "*";
    showPair([$char_x, $char_y]);
    echo "=";
    showMatrix($matrix_a);
    echo "*";
    showPair([$x, $y]);
    echo "=";
    showPair([$a * $x + $b * $y, $c * $x + $d * $y]);
    echo "=";
    showPair([$char_codes_list[mod27($a * $x + $b * $y)], $char_codes_list[mod27($c * $x + $d * $y)]]);
}

function multiply(array $matrix_a, array $pair_b)
{
    showMultiply($matrix_a, $pair_b);
    [[$a, $b], [$c, $d]] = $matrix_a;
    // $divide = 1/($a*$d-$b*$c);
    // $a*=$divide;
    // $b*=$divide;
    // $c*=$divide;
    // $d*=$divide;
    [$x, $y] = $pair_b;
    return [$a * $x + $b * $y, $c * $x + $d * $y];
}
function mod27($a)
{
    global $charlist;
    $mod_val = mb_strlen($charlist) - 1;
    return ($mod_val + ($a % $mod_val)) % $mod_val;
}


function encrypt($message, $base)
{
    global $char_codes, $char_codes_list;
    $message_encrypted = "";
    $ccpairslist = [];
    echo "<div class=messagepairs>";
    echo "message: $message ";
    echo "in pairs = ";
    // echo "<wbr/>";
    foreach (mb_str_split($message, 2) as $char) {
        echo "<div class=equationstart>";
        $chars = mb_str_split($char);
        $char_codes_pair = [$char_codes[$chars[0]], $char_codes[$chars[1]]];
        $ccpairslist[] = $char_codes_pair;
        showPair($chars);
        echo "=>";
        showPair($char_codes_pair);
        // echo "<wbr/>";
        echo "</div>";
    }
    echo "</div>";
    foreach ($ccpairslist as $char_codes_pair) {
        echo "<div class=equationstart>";
        $char_codes_encrypted = multiply($base, $char_codes_pair);
        $char_codes_encrypted[0] = mod27($char_codes_encrypted[0]);
        $char_codes_encrypted[1] = mod27($char_codes_encrypted[1]);
        $message_encrypted .= $char_codes_list[$char_codes_encrypted[0]] . $char_codes_list[$char_codes_encrypted[1]];
        echo "</div>";
    }
    return $message_encrypted;
}

function decrypt($message, $decrypt_base)
{
    return encrypt($message, $decrypt_base);
}
$message;
$baseStr;
$echostr="";
$preoutput ="";
ob_start();
$baseArr=[1,2,3,7];
if(isset($_REQUEST["base"])){
    if(is_array($_REQUEST["base"])){
        $baseArr=$_REQUEST["base"];
    }
    else{
        $baseArr=str_contains(",",$_REQUEST["base"])?explode(",",$_REQUEST["base"]):str_split($_REQUEST["base"]);
    }
}
if (count($baseArr) != 4) $baseArr = [1, 2, 3, 7];
if (isset($_REQUEST["message"]) and $_REQUEST["message"] != "") {

    [$a, $b, $c, $d] = $baseArr;
    if ($a * $d - $b * $c != 1) {
        echo "bad determinant, must be one<br>\n";
        $baseArr = [1, 2, 3, 7];
    }
    $baseStr = implode(",", $baseArr);

    $base = [[(int)$baseArr[0], (int)$baseArr[1]], [(int)$baseArr[2], (int)$baseArr[3]]];
    $baseInv = [[(int)$baseArr[3], -(int)$baseArr[1]], [-(int)$baseArr[2], (int)$baseArr[0]]];
    $message = $_REQUEST["message"];
    $n_message = "";
    foreach (mb_str_split($message) as $char) {
        if (!isset($char_codes[$char])) $char = iconv('UTF-8', 'ASCII//TRANSLIT', $char);
        if (!isset($char_codes[$char])) $char = " ";
        $n_message .= $char;
    }
    if (mb_strlen($message) % 2 == 1) $n_message .= " ";
    $message = $n_message;
    $action = $_REQUEST["action"] ?? "encrypt";
    echo "<div class=calculshow>";
    echo "<div class=equationstart>";
    echo "key:";
    showMatrix($base);
    echo "reverse key:";
    showMatrix($baseInv);
    echo "</div>";
    $echostr.="<div class=result>";
    $echostr.="<fieldset class=type><legend>Type:</legend>$action</fieldset>";
    $echostr.="<fieldset class=message><legend>Message:</legend>$message</fieldset>";
    $echostr.="<div class=lineBases><fieldset class=base><legend>Base:</legend><div class=equationstart>".strMatrix($base)."</div></fieldset>";
    $echostr.="<fieldset class=base><legend>Reverse Base:</legend><div class=equationstart>".strMatrix($baseInv)."</div></fieldset></div>";
    $echostr.="<fieldset class=encrypted>";
    $echostr.="<legend>Result message</legend>";
    if ($action == "decrypt") {
        $echostr.=decrypt($message, $baseInv);
    } else {
        $echostr.=encrypt($message, $base);
    }
    $echostr.="</fieldset>";
    $echostr.="</div>";
    echo "</div>";
}
$preoutput = ob_get_clean();
ob_end_clean();
?>
<body>
<div class=superLine>
<?=$echostr?>
<form action="" method="get">
    <input type=text name="message" value="<?= htmlspecialchars($message ?? "") ?>">
    <!-- <input type=text name="base" value="<?= htmlspecialchars($baseStr ?? "1,2,3,7") ?>" required=false optional> -->
    <div class=equationstart>
        <table>
            <tr>
                <td><input type=number name="base[]" value="<?= htmlspecialchars($baseArr[0] ?? "1") ?>" required=true></td>
                <td><input type=number name="base[]" value="<?= htmlspecialchars($baseArr[1] ?? "2") ?>" required=true></td>
            </tr>
            <tr>
                <td><input type=number name="base[]" value="<?= htmlspecialchars($baseArr[2] ?? "3") ?>" required=true></td>
                <td><input type=number name="base[]" value="<?= htmlspecialchars($baseArr[3] ?? "7") ?>" required=true></td>
            </tr>
        </table>
    </div>
    <input type=submit name="action" value="encrypt">
    <input type=submit name="action" value="decrypt">
</form>
</div>
<details class=preoutput>
    <summary>show debug</summary>
    <?= $preoutput ?>
</div>

<style>
    body{
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        width:100%;
        margin:0px;
        padding:0px;
        background-color: #f0f0f0;
    }
    .preoutput{
        
        max-width: 80%;
        width:80%;
        border:2px solid black;
        border-radius: 5px;
        padding: 10px;
        margin: 10px;
        .messagepairs{
            display:flex;flex-direction: row;flex-wrap: wrap;align-items: center;border:1px solid black;
        }
    }
    .superLine{
        display:flex;
        flex-direction:row;
    }
    form {
        display: flex;
        justify-content: center;
        margin: 10px;
        flex-direction: column;

        input {
            margin: 5px;
            padding:10px;
            border-radius: 5px;
            border: 1px solid black;
        }
    }
    .equationstart {
        display:flex;
        /* justify-content: center; */
        align-items: center;
        margin: 10px;
        table {
            display:block;
            margin: 0 10px;
            border-collapse: collapse;
            border: 2px solid black;
            border-radius: 5px;
            border-top: 0px solid transparent;
            border-bottom: 0px solid transparent;

            td {
                padding: 0 5px;
                border: 0px solid transparent;
                text-align: center;
            }
        }
    }
    .result{
        display:flex;
        border:2px solid black;
        border-radius: 5px;
        flex-direction: column;
        padding: 10px;
        margin: 10px;
        .lineBases{
            display:flex;
            flex-direction:row;
        }
    }
</style>
</body>