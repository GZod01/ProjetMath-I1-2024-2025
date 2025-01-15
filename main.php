<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type:text/html;charset=utf-8');
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
function showMatrix($matrix)
{
    echo strMatrix($matrix);
}
function strMatrix($t)
{
    $stroutput = "<table border=1>";
    foreach ($t as $row) {
        $stroutput .= "<tr>";
        if (is_array($row)) {
            foreach ($row as $cell) {
                $stroutput .= "<td>$cell</td>";
            }
        } else {
            $stroutput .= "<td>$row</td>";
        }
        $stroutput .= "</tr>";
    }
    $stroutput .= "</table>";
    return $stroutput;
}
function matrixMul(array $mat, array $pair)
{
    if (sizeof($pair) != sizeof($mat)) {
        trigger_error("Pair must have size of Mat",E_USER_ERROR);
    }
    $res = array_fill(0, sizeof($pair), 0);
    for ($i = 0; $i < sizeof($pair); $i++) {
        for ($j = 0; $j < sizeof($pair); $j++) {
            $res[$i] += $mat[$i][$j] * $pair[$j]; // Correction : += au lieu de =
        }
    }
    return $res;
}
function showPair($pair)
{
    echo strMatrix($pair);
}
function charCodePair($pair)
{
    global $char_codes_list;
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $npair[$i] = $char_codes_list[mod27($pair[$i])];
    }
    return $npair;
}
function rCharCodePair($pair)
{
    global $char_codes;
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $npair[$i] = $char_codes[$pair[$i]];
    }
    return $npair;
}
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

function multiply(array $matrix_a, array $pair_b)
{
    showMultiply($matrix_a, $pair_b);
    return matrixMul($matrix_a, $pair_b);
}
function mod27($a)
{
    global $charlist;
    $mod_val = mb_strlen($charlist) - 1;
    return ($mod_val + ($a % $mod_val)) % $mod_val;
}
function mod27Mat($pair)
{
    $npair = [];
    for ($i = 0; $i < sizeof($pair); $i++) {
        $npair[$i] = mod27($pair[$i]);
    }
    return $npair;
}
function encrypt($message, $base, $rows_amount)
{
    global $char_codes, $char_codes_list;
    $message_encrypted = "";
    $ccpairslist = [];
    echo "<div class=messagepairs>";
    echo "message: $message ";
    echo "in pairs = ";
    // echo "<wbr/>";
    foreach (mb_str_split($message, $rows_amount) as $char) {
        echo "<div class=equationstart>";
        $chars = mb_str_split($char);
        $char_codes_pair = rCharCodePair($chars);
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
        $char_codes_encrypted = mod27Mat($char_codes_encrypted);
        $message_encrypted .= implode("", charCodePair($char_codes_encrypted));
        echo "</div>";
    }
    return $message_encrypted;
}

function decrypt($message, $decrypt_base, $rows_amount)
{
    return encrypt($message, $decrypt_base, $rows_amount);
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
function castIntArray($arr)
{
    $narr = [];
    foreach ($arr as $a) {
        $narr[] = (int)$a;
    }
    return $narr;
}

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
        $det += $mat[0][$i] * pow(-1, $i) * calculDet($subMat);
    }

    return $det;
}
function arrToMatrix($baseArr, $n)
{
    $base = [];
    $k = 0;
    for ($i = 0; $i < $n; $i++) {
        for ($j = 0; $j < $n; $j++) {
            // echo $k;
            $base[$i][$j] = $baseArr[$k];
            $k++;
        }
    }
    return $base;
}
function matInv($base, $n)
{
    global $charlist;
    // Vérifier si la matrice est carrée
    if (count($base) != $n || count($base[0]) != $n) {
        trigger_error("Erreur : La matrice doit être carrée.",E_USER_ERROR);
        return;
    }

    // Cas de base : matrice 2x2
    if ($n == 2) {
        $a = $base[0][0];
        $b = $base[0][1];
        $c = $base[1][0];
        $d = $base[1][1];

        $determinant = ($a * $d) - ($b * $c);

        // Calculer l'inverse modulaire du déterminant
        $invDet = inverseModulaire($determinant, mb_strlen($charlist));

        // Vérifier si l'inverse modulaire existe
        if ($invDet === null) {
            trigger_error("Erreur : La matrice n'est pas inversible.",E_USER_ERROR);
            return;
        }

        // Multiplier chaque élément de la matrice inverse par l'inverse modulaire
        return [
            [mod27($d * $invDet), mod27(-$b * $invDet)],
            [mod27(-$c * $invDet), mod27($a * $invDet)]
        ];
    }
    // Pour les matrices de taille n > 2, utiliser la méthode de Gauss-Jordan
    else {
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

        // Élimination de Gauss-Jordan
        for ($i = 0; $i < $n; $i++) {
            // Trouver le pivot
            $pivot = $augmentee[$i][$i];
            if ($pivot == 0) {
                // Trouver une ligne avec un pivot non nul et échanger les lignes
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
                    trigger_error("Erreur : La matrice n'est pas inversible.",E_USER_ERROR);
                    return;
                }
            }

            // Normaliser la ligne du pivot
            for ($j = 0; $j < 2 * $n; $j++) {
                $augmentee[$i][$j] /= $pivot;
            }

            // Éliminer les autres éléments de la colonne du pivot
            for ($k = 0; $k < $n; $k++) {
                if ($k != $i) {
                    $facteur = $augmentee[$k][$i];
                    for ($j = 0; $j < 2 * $n; $j++) {
                        $augmentee[$k][$j] -= $facteur * $augmentee[$i][$j];
                    }
                }
            }
        }

        // Extraire l'inverse de la matrice augmentée
        $inverse = [];
        for ($i = 0; $i < $n; $i++) {
            $inverse[$i] = array_slice($augmentee[$i], $n);
        }

        // Calculer le déterminant de la matrice originale
        $determinant = calculDet($base, $n);

        // Calculer l'inverse modulaire du déterminant
        $invDet = inverseModulaire($determinant, mb_strlen($charlist));

        // Vérifier si l'inverse modulaire existe
        if ($invDet === null) {
            trigger_error("Erreur : La matrice n'est pas inversible.",E_USER_ERROR);
            return;
        }

        // Multiplier chaque élément de la matrice inverse par l'inverse modulaire
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $inverse[$i][$j] = mod27($inverse[$i][$j] * $invDet);
            }
        }

        return $inverse;
    }
}
function inverseModulaire($a, $m)
{
    for ($x = 1; $x < $m; $x++) {
        if (($a * $x) % $m == 1) {
            return $x;
        }
    }
    return null;
}
function minor($base, $i, $j)
{
    $n = sizeof($base);
    $minor = [];
    for ($ii = 0; $ii < $n; $ii++) {
        for ($jj = 0; $jj < $n; $jj++) {
            if ($ii != $i and $jj != $j) {
                $minor[$ii - ($ii > $i)][$jj - ($jj > $j)] = $base[$ii][$jj];
            }
        }
    }
    return $minor;
}
if (isset($_REQUEST["message"]) and $_REQUEST["message"] != "") {
    if (sizeof($baseArr) < $rows_amount) {
        echo "bad base, must have rows_amount rows<br>\n";
        $baseArr = [1, 2, 3, 7];
        $rows_amount = 2;
    }
    if (inverseModulaire(calculDet($base, $rows_amount), mb_strlen($charlist)) === null) {
        echo "mat not inversible";
        $baseArr = [1, 2, 3, 7];
        $rows_amount = 2;
    }
    $baseStr = implode(",", $baseArr);

    $base = arrToMatrix($baseArr, $rows_amount);
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
    if ($action == "decrypt") {
        $echostr .= decrypt($message, $baseInv, $rows_amount);
    } else {
        $echostr .= encrypt($message, $base, $rows_amount);
    }
    $echostr .= "</fieldset>";
    $echostr .= "</div>";
    echo "</div>";
}
$preoutput = ob_get_clean();
ob_end_clean();
?>

<body>
    <div class=superLine>
        <?= $echostr ?>
        <form action="" method="get">
            <input type=text name="message" value="<?= htmlspecialchars($message ?? "") ?>">
            <label for=rows_amount>Nombre de colonnes (appuyer sur encrypter pour actualiser la vue d'entrée de la matrice ci-dessous):
                <input type=number name="rows_amount" value="<?= htmlspecialchars($rows_amount) ?>" required=true></label>
            <!-- <input type=text name="base" value="<?= htmlspecialchars($baseStr ?? "1,2,3,7") ?>" required=false optional> -->
            <div class=equationstart>
                <table>
                    <?php for ($i = 0; $i < $rows_amount; $i++) { ?>
                        <tr>
                            <?php for ($j = 0; $j < $rows_amount; $j++) { ?>
                                <td><input type=number name="base[]" value="<?= htmlspecialchars($base[$i][$j]) ?>" required=true></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
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
            body {
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                width: 100%;
                margin: 0px;
                padding: 0px;
                background-color: #f0f0f0;
            }

            .preoutput {

                max-width: 80%;
                width: 80%;
                border: 2px solid black;
                border-radius: 5px;
                padding: 10px;
                margin: 10px;

                .messagepairs {
                    display: flex;
                    flex-direction: row;
                    flex-wrap: wrap;
                    align-items: center;
                    border: 1px solid black;
                }
            }

            .superLine {
                display: flex;
                flex-direction: row;
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
                    border: 1px solid black;
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

            .result {
                display: flex;
                border: 2px solid black;
                border-radius: 5px;
                flex-direction: column;
                padding: 10px;
                margin: 10px;

                .lineBases {
                    display: flex;
                    flex-direction: row;
                }
            }
        </style>
</body>