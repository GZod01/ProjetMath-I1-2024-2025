<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/html; charset=utf-8');
mb_internal_encoding("utf-8");
?>
<title>SuperEncrypt</title>
<meta charset="utf-8">
<?php

$charlist = " ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz&é(-è_çà)=$1234567890?.!,;:*/\\+-'\"#{[|@]}%µ§";
$char_codes_list = mb_str_split($charlist);
$char_codes = [];


foreach ($char_codes_list as $i => $cc) {
    $char_codes[$cc] = $i;
}

// afficher matrice
function showMatrix($matrix)
{
    echo strMatrix($matrix);
}

function strToOkHTML($str){
    $nstr = htmlspecialchars($str);
    $nstr = str_replace(" ", "&#9633;", $nstr);
    return $nstr;
}

// matrice vers texte
function strMatrix($t)
{
    $stroutput = "<table border=1>";
    foreach ($t as $row) {
        $stroutput .= "<tr>";
        if (is_array($row)) {
            foreach ($row as $cell) {
                $ncell = strToOkHTML($cell);
                $stroutput .= "<td>$ncell</td>";
            }
        } else {
            $nrow = strToOkHTML($row);
            $stroutput .= "<td>$nrow</td>";
        }
        $stroutput .= "</tr>";
    }
    $stroutput .= "</table>";
    return $stroutput;
}

// multiplier matrice et pair
function matrixMul(array $mat, array $pair)
{
    if (sizeof($pair) != sizeof($mat)) {
        trigger_error("Pair must have size of Mat", E_USER_ERROR);
    }
    $res = array_fill(0, sizeof($pair), 0);
    for ($i = 0; $i < sizeof($pair); $i++) {
        for ($j = 0; $j < sizeof($pair); $j++) {
            $res[$i] += $mat[$i][$j] * $pair[$j];
        }
    }
    return $res;
}

// afficher paire
function showPair($pair)
{
    echo strMatrix($pair);
}

// paire de code vers paire de lettres
function charCodePair($pair)
{
    global $char_codes_list;
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $index = round(mod27($pair[$i]));
        $npair[$i] = $char_codes_list[$index];
    }
    return $npair;
}

// paire de lettres vers paire de code
function rCharCodePair($pair)
{
    global $char_codes;
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $char = $pair[$i];
        if (isset($char_codes[$char])) {
            $npair[$i] = round($char_codes[$char]);
        } else {
            $npair[$i] = 0;
        }
    }
    return $npair;
}

// afficher multiplication
function showMultiply($matrix_a, $pair_b)
{
    global $char_codes_list;
    showMatrix($matrix_a);
    echo "*";
    showPair(charCodePair($pair_b));
    echo "=";
    showMatrix($matrix_a);
    echo "*";
    showPair($pair_b);
    echo "=";
    $retPair = matrixMul($matrix_a, $pair_b);
    showPair($retPair);
    echo "=";
    showPair(charCodePair($retPair));
}

// multiplier matrice par pair
function multiply(array $matrix_a, array $pair_b)
{
    showMultiply($matrix_a, $pair_b);
    return matrixMul($matrix_a, $pair_b);
}

// modulo {sizeof($charlist)}
function mod27($a)
{
    global $charlist;
    $mod_val = mb_strlen($charlist);
    return fmod(($mod_val + fmod($a, $mod_val)), $mod_val);
}

// return le pair avec tous les éléments modulo {sizeof($charlist)}
function mod27Mat($pair)
{
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $npair[$i] = mod27($pair[$i]);
    }
    return $npair;
}

// Encrypter
function encrypt($message, $base, $rows_amount)
{
    global $char_codes, $char_codes_list;
    $message_encrypted = "";
    $ccpairslist = [];
    echo "<div class=messagepairs>";
    echo "message: $message ";
    echo "in pairs = ";
    foreach (mb_str_split($message, $rows_amount) as $char) {
        echo "<div class=equationstart>";
        $chars = mb_str_split($char);
        $char_codes_pair = rCharCodePair($chars);
        $ccpairslist[] = $char_codes_pair;
        showPair($chars);
        echo "=>";
        showPair($char_codes_pair);
        echo "</div>";
    }
    echo "</div>";
    foreach ($ccpairslist as $char_codes_pair) {
        echo "<div class=equationstart>";
        $char_codes_encrypted = multiply($base, $char_codes_pair);
        $char_codes_encrypted = mod27Mat($char_codes_encrypted);
        $message_encrypted .= implode("", charCodePair($char_codes_encrypted));
        echo "</div>";
    }
    return $message_encrypted;
}

//Decrypter (appelle encrypt avec base inversée)
function decrypt($message, $decrypt_base, $rows_amount)
{
    return encrypt($message, $decrypt_base, $rows_amount);
}

// Plus Grand Commun Diviseur
function pgcd($a, $b)
{
    while ($b != 0) {
        $temp = $a % $b;
        $a = $b;
        $b = $temp;
    }
    return $a;
}

// inverse modulaire
function inverseModulaire($a, $m)
{
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return null;
}

// modifier la matrice en mettant des nombres premiers pour simplification
function modifierMatriceAvecPremiers($matrice)
{
    global $charlist;
    $m = mb_strlen($charlist);

    $nombresPremiers = [2, 3, 5, 7, 11, 13, 17, 19, 23, 29, 31, 37, 41, 43, 47, 53, 59, 61, 67, 71, 73, 79];

    foreach ($nombresPremiers as $premier) {
        if (pgcd($m, $premier) != 1) {
            trigger_error("Erreur : m doit être premier avec tous les nombres premiers.");
        }
    }

    $inverses = [];
    foreach ($nombresPremiers as $premier) {
        $inverses[] = inverseModulaire($premier, $m);
    }

    $n = count($matrice);
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $matrice[$i][$j] = $inverses[($i * $n + $j) % count($inverses)];
        }
    }

    return $matrice;
}

$base = [[1, 2], [3, 7]];
$rows_amount = 2;
$message;
$baseStr;
$echostr = "";
$preoutput = "";
ob_start();
$baseArr = [1, 2, 3, 7];

if (isset($_REQUEST["rows_amount"])) {
    $rows_amount = $_REQUEST["rows_amount"];
}

if (isset($_REQUEST["base"])) {
    if (is_array($_REQUEST["base"])) {
        $baseArr = castIntArray($_REQUEST["base"]);
        $base = arrToMatrix($baseArr, $rows_amount);
    } else {
        $baseArr = castIntArray(str_contains(",", $_REQUEST["base"]) ? explode(",", $_REQUEST["base"]) : str_split($_REQUEST["base"]));
    }
}

// pour un array avec uniquement des entiers
function castIntArray($arr)
{
    $narr = [];
    foreach ($arr as $a) {
        $narr[] = (int)$a;
    }
    return $narr;
}

// déterminant
// retourne la seule valeur pour une matrice 1x1, le det pour une 2x2 sinon pour les autres calcul par sous matrices récursivement
function calculDet($mat)
{
    $n = count($mat);
    if ($n == 1) {
        return $mat[0][0];
    }
    if ($n == 2) {
        return $mat[0][0] * $mat[1][1] - $mat[0][1] * $mat[1][0];
    }
    $det = 0;
    for ($i = 0; $i < $n; $i++) {
        $subMat = [];
        for ($j = 1; $j < $n; $j++) {
            $row = [];
            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $row[] = $mat[$j][$k];
                }
            }
            $subMat[] = $row;
        }
        $det += pow(-1, $i) * $mat[0][$i] * calculDet($subMat);
    }

    return $det;
}

// array vers matrice
function arrToMatrix($baseArr, $n)
{
    $base = [];
    $k = 0;
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            $base[$i][$j] = $baseArr[$k]??0;
            $k++;
        }
    }
    return $base;
}
// matrice inversée
function matInv($base, $n)
{
    global $charlist;
    $m = mb_strlen($charlist);
    if (count($base) != $n || count($base[0]) != $n) {
        trigger_error("Erreur : La matrice doit être carrée.", E_USER_ERROR);
        return null;
    }
    $identite = [];
    for ($i = 0; $i < $n; $i++) {
        $identite[$i] = [];
        for ($j = 0; $j < $n; $j++) {
            $identite[$i][$j] = ($i == $j) ? 1 : 0;
        }
    }
    $augmentee = [];
    for ($i = 0; $i < $n; $i++) {
        $augmentee[$i] = array_merge($base[$i], $identite[$i]);
    }
    for ($i = 0; $i < $n; $i++) {
        $pivot = $augmentee[$i][$i];
        if ($pivot == 0) {
            for ($k = $i + 1; $k < $n; $k++) {
                if ($augmentee[$k][$i] != 0) {
                    $temp = $augmentee[$i];
                    $augmentee[$i] = $augmentee[$k];
                    $augmentee[$k] = $temp;
                    $pivot = $augmentee[$i][$i];
                    break;
                }
            }
            if ($pivot == 0) {
                return null;
            }
        }
        $invPivot = inverseModulaire($pivot, $m);
        for ($j = 0; $j < 2 * $n; $j++) {
            $augmentee[$i][$j] = mod27($augmentee[$i][$j] * $invPivot);
        }
        for ($k = 0; $k < $n; $k++) {
            if ($k != $i) {
                $facteur = $augmentee[$k][$i];
                for ($j = 0; $j < 2 * $n; $j++) {
                    $augmentee[$k][$j] = mod27($augmentee[$k][$j] - $facteur * $augmentee[$i][$j]);
                }
            }
        }
    }
    $inverse = [];
    for ($i = 0; $i < $n; $i++) {
        $inverse[$i] = array_slice($augmentee[$i], $n);
    }
    return $inverse;
}

if (isset($_REQUEST["message"]) and $_REQUEST["message"] != "") {
    if (sizeof($baseArr) < $rows_amount) {
        echo "bad base, must have rows_amount rows<br>\n";
        $baseArr = [1, 2, 3, 7];
        $rows_amount = 2;
    }

    $baseStr = implode(",", $baseArr);
    $base = arrToMatrix($baseArr, $rows_amount);

    if (calculDet($base, $rows_amount) == 0) {
        echo "La matrice n'est pas inversible. Modification avec des nombres premiers...<br>";
        $base = modifierMatriceAvecPremiers($base);
    }

    $baseInv = matInv($base, $rows_amount);
    $message = $_REQUEST["message"];
    $n_message = "";

    foreach (mb_str_split($message) as $char) {
        if (!isset($char_codes[$char])) $char = iconv('UTF-8', 'ASCII//TRANSLIT', $char);
        if (!isset($char_codes[$char])) $char = " ";
        $n_message .= $char;
    }

    while (mb_strlen($n_message) % $rows_amount != 0) $n_message .= " ";
    $message = $n_message;
    $action = $_REQUEST["action"] ?? "encrypt";
    echo "<div class=calculshow>";
    echo "<div class=equationstart>";
    echo "key:";
    showMatrix($base);
    echo "reverse key:";
    showMatrix($baseInv);
    echo "</div>";
    $echostr .= "<div class=result>";
    $echostr .= "<fieldset class=type><legend>Type:</legend>$action</fieldset>";
    $echostr .= "<fieldset class=message><legend>Message:</legend>$message</fieldset>";
    $echostr .= "<div class=lineBases><fieldset class=base><legend>Base:</legend><div class=equationstart>" . strMatrix($base) . "</div></fieldset>";
    $echostr .= "<fieldset class=base><legend>Reverse Base:</legend><div class=equationstart>" . strMatrix($baseInv) . "</div></fieldset></div>";
    $echostr .= "<fieldset class=encrypted>";
    $echostr .= "<legend>Result message</legend>";
    $echostr .= "<span id=alerttextcopied>Texte copié</span>";
    $echostr .= "<span id=superresulttocopy onclick=\"copyClipBoard(this)\">";

    if ($action == "decrypt") {
        $echostr .= htmlspecialchars(decrypt($message, $baseInv, $rows_amount));
    } else {
        $echostr .= htmlspecialchars(encrypt($message, $base, $rows_amount));
    }
    $echostr.="</span>";
    $echostr .= "</fieldset>";
    $echostr.="<p>Cliquez sur le résultat pour copier dans le presse-papier</p>";
    $echostr .= "</div>";
    echo "</div>";
}

$preoutput = ob_get_clean();
ob_end_clean();
?>

<body>
    <script>
        function copyClipBoard(el){
            navigator.clipboard.writeText(el.innerText);
            document.querySelector("#alerttextcopied").className="";
            document.querySelector('#alerttextcopied').className="visible";
        }
    </script>
    <div class=superLine>
        <?= $echostr ?>
        <form action="" class=form method="get">
            <input type=text name="message" id="message" value="<?= htmlspecialchars($message ?? "") ?>">
            <label for=rows_amount>Nombre de colonnes (appuyer sur encrypter pour actualiser la vue d'entrée de la matrice ci-dessous):
                <input type=number name="rows_amount" id="rows_amount" value="<?= htmlspecialchars($rows_amount) ?>" required=true></label>
            <div class=equationstart>
                <table>
                    <?php for ($i = 0; $i < $rows_amount; $i++) { ?>
                        <tr>
                            <?php for ($j = 0; $j < $rows_amount; $j++) { ?>
                                <td><input type=number name="base[]" id="base<?= strval($i) . strval($j); ?>" value="<?= htmlspecialchars($base[$i][$j]) ?>" required=true></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </table>
            </div>
            <input type=submit name="action" id="action" value="encrypt">
            <input type=submit name="action" id="action" value="decrypt">
        </form>
    </div>
    <details class=preoutput>
        <summary>show debug</summary>
        <?= $preoutput ?>
    </details>
    <button onclick="document.querySelector(':root').className='matrix';">ToMat</button>

    <style>
        :root{
            --bgcolor : #f0f0f0;
            --bgdarken: #e0e0e0;
            --color:black;
            transition: all .5s ease;
        }
        :root.matrix{
            --bgcolor:#101010;
            --bgdarken:#303030;
            --color:green;
            transition:all .5s ease;
        }
        body {
            color: var(--color);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            width: 100%;
            margin: 0px;
            padding: 0px;
            background-color:var(--bgcolor);
        }
        input{
            background:var(--bgdarken);
            color:var(--color);
        }
        fieldset{
            border-color:var(--color);
        }

        .form .equationstart {
            max-width: 60vw;
            overflow: auto;
        }

        .preoutput {
            max-width: 80%;
            width: 80%;
            border: 2px solid var(--color);
            border-radius: 5px;
            padding: 10px;
            margin: 10px;

            .messagepairs {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                align-items: center;
                border: 1px solid var(--color);
            }
        }

        .superLine {
            display: flex;
            flex-direction: <?=$rows_amount<=5?"row":"column"?>;
        }

        form {
            display: flex;
            justify-content: center;
            margin: 10px;
            flex-direction: column;

            input {
                margin: 5px;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid var(--color);
            }
        }

        .equationstart {
            display: flex;
            /* justify-content: center; */
            align-items: center;
            margin: 10px;

            table {
                display: block;
                margin: 0 10px;
                border-collapse: collapse;
                border: 2px solid var(--color);
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

        .result {
            display: flex;
            border: 2px solid var(--color);
            border-radius: 5px;
            flex-direction: column;
            padding: 10px;
            margin: 10px;

            .lineBases {
                display: flex;
                flex-direction: row;
            }

            .equationstart {
                max-width: 30vw;
                overflow: auto;
            }
        }
        .copyright{
            font-size: 0.7em;
            font-family: 'Courier New', Courier, monospace;
        }
        #alerttextcopied{
            height:calc(20px + 2em);
            display:none;
            background:lime;
            color:white;
            padding:10px;
            border-radius:5px;
            font-size:1.5em;
            font-weight:bold;
            text-align:center;
        }
        #alerttextcopied.visible{
            display:block;
            animation:progDisappear 3s;
        }
        @keyframes progDisappear{
            0%{
                opacity:1;
                height:calc(20px + 2em);
            }
            80%{
                opacity:1;
                height:
            }
            99%{
                oppacity:0;
                height:0;
            }
            100%{
                display:none;
            }
        }
    </style>
    <p class=copyright>Copyright &copy; 2024 <strong><a href="https://gzod01.fr">GZod01</a> (Aurélien SÉZILLE)</strong></p>
</body>