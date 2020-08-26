<?php
$cpanel = array(
    "session" => "", //Edit
    "host" => "", //Edit 
    "port" => "2083", //Edit
    "username" => "", //Edit
    "apitoken" => "" //Edit
);
$hCaptcha = array(
    "secret" => "", //Edit
    "sitekey" => "" //Edit
);
$emailDomain = "";

$success = 0;// Don't edit

if (isset($_POST['h-captcha-response']) && isset($_POST['email']) && isset($_POST['password'])) {
    $data = array(
        'secret' => $hCaptcha["secret"],
        'response' => $_POST['h-captcha-response']
    );
    $verify = curl_init();
    curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
    curl_setopt($verify, CURLOPT_POST, true);
    curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($verify);
    $responseData = json_decode($response);
    if ($responseData->success) {
        $success = 1;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_PORT => $cpanel["port"],
            CURLOPT_URL => sprintf("https://%s:%s/%s/execute/Email/add_pop?email=%s&password=%s&quota=0&domain=%s&send_welcome_email=1", $cpanel["host"], $cpanel["port"], $cpanel["session"], urlencode($_POST['email']), urlencode($_POST['password']), urlencode($emailDomain)),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                sprintf("authorization: cpanel %s:%s", $cpanel["username"], $cpanel["apitoken"])
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            die("cURL Error #:" . $err);
        } else {
            $search = "already exists!";
            if (preg_match("/{$search}/i", $response)) {
                $success = 1;
            } else {
                $success = 2;
            }
        }
    } else {
        die("Invalid hCaptcha response \n".$response);
    }
} ?>
<html>
<head>
    <script src="https://www.hCaptcha.com/1/api.js" async defer></script>
</head>
<body class="login">

<div id="login-form">
    <?php if ($success == 2): ?>
    <h4>Created email</h4>
    <?php elseif ($success == 1): ?>
    <h4>Was able to verify the hCaptcha, but couldn't create the email</h4>
    <?php elseif ($success==0): ?>
    <div>
        <form id="login" action="" method="POST">
            <input required type="email" class="text" placeholder="Email adress" name="email">
            <input required  minlength="8" pattern="^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\s).*$" title="Please include at least 1 uppercase character, 1 lowercase character, and 1 number." type="password" class="text" placeholder="Password" name="password">
            <div class="h-captcha" data-sitekey="<?php echo $hCaptcha["sitekey"]; ?>"></div>
            <input type="submit" name="submit" value="SUBMIT">
        </form>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
