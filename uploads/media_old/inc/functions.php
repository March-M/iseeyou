<?php

function generate_token($uniqid)
{
    //on génère les deux données pour les insérer dans le token (peut poser problème si on le fait en une ligne)
    $openssl_token = bin2hex(openssl_random_pseudo_bytes(10));
    $time = time();

    //on construit le token
    $token = base64_encode($time.sha1($openssl_token.$uniqid.$time).$openssl_token);
    
    return $token;
}

function verify_token($token, $uniqid)
{
    include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');

    //Extraction des différents morceau du token : Timestamp, hash openssl_random, sha1 de sécurité
    $token_extracted['decoded'] = base64_decode($token);
    $token_extracted['time'] = substr($token_extracted['decoded'], 0, 10);
    $token_extracted['sha1_hash'] = substr($token_extracted['decoded'], 10, 40);
    $token_extracted['openssl_hash'] = substr($token_extracted['decoded'], 50, 70);

    //si le timestamp actuel est plus grand que le timestamp du formulaire + 1800 (30 minute), le token est périmé
    if(time() > $token_extracted['time']+1800)
    {
        //Return 0 pour indiquer l'invaliditée
        return 0;
    }

    //On regénère un hash sha1 avec les valeurs du token (hash openssl, uniqid, timestamp et IP)
    $sha1_hash = sha1($token_extracted['openssl_hash'].$uniqid.$token_extracted['time']);
    
    //On vérifie la validité du hash sha1 du token en le comparant a celui généré au dessus, pour éviter une modification du timestamp, du hash openssl, ou encore une utilisation sur une IP différente
    if($sha1_hash != $token_extracted['sha1_hash'])
    {
        //Return 0 pour indiquer l'invaliditée
        return 0;
    }

    //Jusque la, le token est donc valide. On vérifie s'il n'est pas dans la table des tokens invalide
    $req_token = $mysql->prepare("SELECT count(*) FROM `bidoof_token` WHERE `token` = :token");
    $req_token->bindValue(':token', $token_extracted['decoded'], PDO::PARAM_STR);
    $req_token->execute();
    $count['nbtoken'] = $req_token->fetch();
    $count['nbtoken'] = $count['nbtoken'][0];
    
    //si il y a un nombre de token différent de 0 dans le count, alors il est déjà utilisé
    if(intval($count['nbtoken']) != 0)
    {
        return 0;
    }
    else
    {
        //Sinon, alors le token est valide, on ajoute le token en db pour qu'il ne sois pas réutilisé
        $req_add_token = $mysql->prepare("INSERT INTO `bidoof_token`(`token`, `timestamp`) VALUES (:token,:time)");
        $req_add_token->bindValue(':token', $token_extracted['decoded'], PDO::PARAM_STR);
        $req_add_token->bindValue(':time', $token_extracted['time'], PDO::PARAM_STR);
        $req_add_token->execute();

        //On exécute la routine de supression des tokens qui sont forcément périmé
        $req_dell_token = $mysql->prepare("DELETE FROM `bidoof_token` WHERE `timestamp`+1800 < UNIX_TIMESTAMP()");
        $req_dell_token->execute();
        return 1;
    }
}


//Traduction des dates interne de php qui sont en anglais
function PrettyDate($timestamp, $mode = 1, $when = 1, $year = 1, $prefix = 0)
{
    $months[0] = array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
    $months[1] = array("", "Jan", "Fév", "Mars", "Avr", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc");
    
    $days[0] = array("", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche");
    $days[1] = array("", "Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim");
    
    if (date("j", $timestamp) == 1)
    {
        $daynb = "1<sup>er</sup>";
    }
    else
    {
        $daynb = date("j", $timestamp);
    }
    
    if ($year == 1)
    {
        $year = " " . date("Y", $timestamp);
    }
    else
    {
        $year = "";
    }
    
    if ($prefix == 1)
    {
        $prefix = "le ";
    }
    else
    {
        $prefix = "";
    }
    
    if ($when == 1)
    {
        if (date("d-m-Y") == date("d-m-Y", $timestamp))
        {
            return "Aujourd'hui à " . date("H:i", $timestamp);
        }
        elseif (date("d-m-Y", time()-86400) == date("d-m-Y", $timestamp))
        {
            return "Hier à " . date("H:i", $timestamp);
        }
        else
        {
            return $prefix . $days[$mode][date("N", $timestamp)] . " " . $daynb . " " . $months[$mode][date("n", $timestamp)] . $year . " à " . date("H:i", $timestamp);
        }
    }
    else
    {
        return $prefix . $days[$mode][date("N", $timestamp)] . "  " . $daynb . " " . $months[$mode][date("n", $timestamp)] . $year . " à " . date("H:i", $timestamp);
    }
}

//Fonction pour générer l'identifiant unique pour reconnaire un utilisateur 
function make_session_signature($password, $mail) 
{
    $mail = sha1($mail);
    return sha1($password.$mail);
}
//Fonction servant a détruire complètement le cookie et la session
function Destroy_Session()
{
    //On plombe complètement la session. En premier, la méthode "douce" avec unset sur les variable session, 
    //deux setcookie de valeur négative pour provoquer la supression automatique du cookie suivi par un unset (on sais jamais), puis un session_destroy pour achever la session définitivement. 
    unset($_SESSION['userpass']);
    unset($_SESSION['userid']);
    unset($_SESSION['token']);
    setcookie('userpass', NULL, -1);
    setcookie('userid', NULL, -1);
    unset($_COOKIE['userpass']);
    unset($_COOKIE['userid']);
    session_destroy();
    header("Location: /admin/index.php?deco");
}

//Vérifie la validitée du cookie, si il est valide on le met a jour pour rénover sa durée de validitée, sinon on le détruit lui et la session, si il n'existe pas on renvois true pour la gestion des redirections
function Check_cookie() 
{
    if((isset($_COOKIE['userpass']) && !empty($_COOKIE['userpass'])) && (isset($_COOKIE['userid']) && !empty($_COOKIE['userid'])))
    {
        include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
        $req_user_data = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
        $req_user_data->bindValue(':id', $_COOKIE['userid'], PDO::PARAM_STR);
        $req_user_data->execute();
        $user_data_nb = $req_user_data->rowCount();
       $user_data = $req_user_data->fetch();

        //On vérifie si le résultat de la requete est vide, si il l'est, alors compte inexistant (probablement mot de passe faux car l'email à déjà été vérifié ligne 24)
        if ($user_data_nb == "0") 
        {
            header("Location: /admin/index.php?no");
        }

        if ($_COOKIE['userpass'] == make_session_signature($user_data['password'], $user_data['pseudo']))
        {
            $_SESSION['userid'] = $user_data['id']; //On Réinitialise en session l'ID de l'utilisateur, Au cas ou il y ai eu une bidouille, pour éviter les usurpation
            setcookie("userpass", make_session_signature($user_data['password'], $user_data['pseudo']), time()+60*60*24*100, "/");
            setcookie("userid", $user_data['id'], time()+60*60*24*100, "/");
            return TRUE;
        }
        else 
        {
            Destroy_Session();
            return FALSE;
        }
    }
    else 
    {
        return TRUE;
    }
}

//Vérifie la validitée de la session, si il est valide on le met a jour pour éviter des données faussées, sinon on la détruit.
function Check_session() 
{
    if((isset($_SESSION['userpass']) && !empty($_SESSION['userpass'])) && (isset($_SESSION['userid']) && !empty($_SESSION['userid'])))
    {
        include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
        $req_user_data = $mysql->prepare("SELECT * FROM media_users WHERE id=:id ");
        $req_user_data->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
        $req_user_data->execute();
        $user_data_nb = $req_user_data->rowCount();
        $user_data = $req_user_data->fetch();
    
        //On vérifie si le résultat de la requete est vide, si il l'est, alors compte inexistant (probablement mot de passe faux car l'email à déjà été vérifié ligne 24)
        if ($user_data_nb == "0") {
          header("Location: /admin/index.php?no");
        }

        if ($_SESSION['userpass'] == make_session_signature($user_data['password'], $user_data['pseudo']))
        {
            $_SESSION['userid'] = $user_data['id']; //On Réinitialise en session l'ID de l'utilisateur, Au cas ou il y ai eu une bidouille, pour éviter les usurpation
            return TRUE;
        }
        else 
        {
            Destroy_Session();
            return FALSE;
        }
    }
    else
    {
        Destroy_Session();
        return FALSE;
    }
}

//Fonction pour générer les messages d'alerte simplement
function Generate_Popup_result($result_type, $result_msg)
{
    if ($result_type == 0)
    {
        //Succès de l'opération : code d'erreur 0
        return '<h1><span class="label label-success center">'.$result_msg.'</span></h1>';
    } 
    elseif ($result_type == 1)
    {
        //Probleme survenu : code d'erreur 1
        return '<h1><span class="label label-warning center">'.$result_msg.'</span></h1>';
    }
    elseif ($result_type == 2)
    {
        //gros problème survenu : code d'erreur 2
        return '<h1><span class="label label-danger center">'.$result_msg.'</span></h1>';
    } 
}

function log_update($action)
{
    include($_SERVER['DOCUMENT_ROOT'].'/inc/database.php');
    //Mise à jour du log d'action
    $req_user_ip_update = $mysql->prepare("INSERT INTO `log` (`id`, `id_user`, `date`, `action`, `ip`) VALUES (NULL, :id, UNIX_TIMESTAMP(), :act, :ip);");
    $req_user_ip_update->bindValue(':id', $_SESSION['userid'], PDO::PARAM_STR);
    $req_user_ip_update->bindValue(':act', $action, PDO::PARAM_STR);
    $req_user_ip_update->bindValue(':ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
    $req_user_ip_update->execute();
}

/**
 * PHP Class for handling Google Authenticator 2-factor authentication
 *
 * @author Michael Kliewe
 * @copyright 2012 Michael Kliewe
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link http://www.phpgangsta.de/
 */
class GoogleAuthenticator
{
    protected $_codeLength = 6;
    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     * @return string
     */
    public function createSecret($secretLength = 16)
    {
        $validChars = $this->_getBase32LookupTable();
        unset($validChars[32]);
        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }
    /**
     * Calculate the code, with given secret and point in time
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCode($secret, $timeSlice = null)
    {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }
        $secretkey = $this->_base32Decode($secret);
        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);
        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }
    /**
     * Get QR-Code URL for image, from google charts
     *
     * @param string $name
     * @param string $secret
     * @param string $title
     * @return string
     */
    /*public function getQRCodeGoogleUrl($name, $secret, $title = null) {
        $urlencoded = urlencode('otpauth://totp/'.$name.'?secret='.$secret.'');
    if(isset($title)) {
                $urlencoded .= urlencode('&issuer='.urlencode($title));
        }
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl='.$urlencoded.'';
    }*/
    public function getQRCodeGoogleUrl($username, $domain, $secret) {
        $urlencoded = urlencode('otpauth://totp/'.$username.'?secret='.$secret.'&digits=6&issuer='.$domain);
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl='.$urlencoded.'';
    }
    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $currentTimeSlice time slice if we want use other that time()
     * @return bool
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null)
    {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode == $code ) {
                return true;
            }
        }
        return false;
    }
    /**
     * Set the code length, should be >=6
     *
     * @param int $length
     * @return PHPGangsta_GoogleAuthenticator
     */
    public function setCodeLength($length)
    {
        $this->_codeLength = $length;
        return $this;
    }
    /**
     * Helper class to decode base32
     *
     * @param $secret
     * @return bool|string
     */
    protected function _base32Decode($secret)
    {
        if (empty($secret)) return '';
        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);
        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        for ($i = 0; $i < 4; $i++){
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i])) return false;
        }
        $secret = str_replace('=','', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i+8) {
            $x = "";
            if (!in_array($secret[$i], $base32chars)) return false;
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
            }
        }
        return $binaryString;
    }
    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool $padding
     * @return string
     */
    protected function _base32Encode($secret, $padding = true)
    {
        if (empty($secret)) return '';
        $base32chars = $this->_getBase32LookupTable();
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i++) {
            $binaryString .= str_pad(base_convert(ord($secret[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i = 0;
        while ($i < count($fiveBitBinaryArray)) {
            $base32 .= $base32chars[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
            $i++;
        }
        if ($padding && ($x = strlen($binaryString) % 40) != 0) {
            if ($x == 8) $base32 .= str_repeat($base32chars[32], 6);
            elseif ($x == 16) $base32 .= str_repeat($base32chars[32], 4);
            elseif ($x == 24) $base32 .= str_repeat($base32chars[32], 3);
            elseif ($x == 32) $base32 .= $base32chars[32];
        }
        return $base32;
    }
    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected function _getBase32LookupTable()
    {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
    }
}
?>
