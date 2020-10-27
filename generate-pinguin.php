<?php
session_start();

function check_recaptcha($response)
{
    $ch = curl_init();
    $curlConfig = array(
        CURLOPT_URL => "https://www.google.com/recaptcha/api/siteverify",
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => array(
            'secret' => 'YOUR RECAPTCHA SECRET KEY',
            'response' => $response,
        )
    );
    curl_setopt_array($ch, $curlConfig);
    $result = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($result, true);
    if ($json["success"] && !$_SESSION["checked"]) {
        $_SESSION["checked"] = true;
    }
    return $json["success"];
}

if (!$_SESSION["checked"] && (!isset($_GET["g-recaptcha-response"]) || !check_recaptcha($_GET["g-recaptcha-response"]))) {
    http_response_code(403);
    exit();
} else {

    header('Content-type: image/png');
    header('Cache-Control: must-revalidate');
    http_response_code(200);

    $font_map = array(
//    Examples of Font name and Font file mapping
//    Get them from Google Fonts
        "Roboto Condensed Bold" => "static/fonts/RobotoCondensed-Bold.ttf",
    );

// Set your defualt values here
    $pengu_image = "static/penguimages/penguHappy.png";

// Pinguin dimension ratio to the total width and height om image
    $w_aspect_ratio = 4 / 5;
    $h_aspect_ratio = 4 / 5;

    $bg_color = "rgb(90, 226, 80)";
    $text_color = "rgb(11, 139, 0)";
    $text = "";
    $text_size = 80;
    $font_name = "Roboto Condensed Bold";
    $x_offset = 0;
    $y_offset = 80;
    $size = 541.25;

    $text_kerning = 0;

    if (isset($_GET["model"])) {
        if ($_GET["model"] == "UL") {
            $bg_color = "rgb(255,62,62)";
            $text_color = "rgb(130,20,20)";
        }
    }

    isset($_GET["pengu_style"]) && $pengu_image = "static/penguimages/" . $_GET["pengu_style"] . ".png";
    isset($_GET["text_color"]) && $text_color = $_GET["text_color"];
    isset($_GET["text"]) && $text = $_GET["text"];
    isset($_GET["text_size"]) && $text_size = floatval($_GET["text_size"]);
    isset($_GET["font_name"]) && in_array($_GET["font_name"], $font_map) && $font_name = $_GET["font_name"];
    isset($_GET["x_offset"]) && $x_offset = floatval($_GET["x_offset"]);
    isset($_GET["y_offset"]) && $y_offset = floatval($_GET["y_offset"]);
    isset($_GET["bg_color"]) && $bg_color = $_GET["bg_color"];
    isset($_GET["size"]) && $size = floatval($_GET["size"]);
    isset($_GET["text_kerning"]) && $text_kerning = floatval($_GET["text_kerning"]);

    $background = new ImagickDraw();
    $background->setFillColor($bg_color);
    $background->setGravity(Imagick::GRAVITY_CENTER);
    $background->circle($size / 2, $size / 2, 425 * $size / 500, 425 * $size / 500);

    $text_drawer = new ImagickDraw();
    $text_drawer->setFillColor($text_color);
    $text_drawer->setGravity(Imagick::GRAVITY_CENTER);
    $text_drawer->setFontSize($text_size);
    $text_drawer->setFont($font_map[$font_name]);
    $text_drawer->setTextKerning($text_kerning);
    $text_drawer->annotation($x_offset, $y_offset, $text);

    $pengu = new Imagick($pengu_image);
    $pengu->scaleImage($w_aspect_ratio * $size, $h_aspect_ratio * $size);

    $image = new Imagick();
    $image->newImage($size, $size, 'transparent');
    $image->setImageFormat("png");
    $image->drawImage($background);
    $image->compositeImageGravity($pengu, Imagick::COMPOSITE_ATOP, Imagick::GRAVITY_CENTER);
    $image->drawImage($text_drawer);

    echo $image->getImageBlob();
    $image->clear();
}