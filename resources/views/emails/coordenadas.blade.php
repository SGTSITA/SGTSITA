<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Correo</title>
</head>
<body style="font-family: sans-serif;text-align: center;">

    

    <div style="text-align: center; margin: 20px 0;">
        <a href="{{ $datos['link'] }}" style="
            display: inline-block;
            padding: 12px 24px;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        ">
            Acceder al enlace
        </a>
    </div>

    <p style="text-align: center;">Gracias,<br>{{ config('app.name') }}</p>

</body>
</html>