<!doctype html>
<html>

<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <style>
        * {
            text-align: center;
            margin: 0;
            padding: 0;
            font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
        }

        p {
            margin-top: 1em;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <p><?php echo esc_html($message); ?></p>
</body>

</html>