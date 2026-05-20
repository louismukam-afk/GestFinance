<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon de commande {{ $bon->id }}</title>
    @include('Admin.MesBons.partials.document_styles')
    <style>
        .bon-document {
            background: #fff;
            padding: 0;
        }

        .bon-sheet {
            width: auto;
            min-height: auto;
            margin: 0;
            padding: 0;
            box-shadow: none;
        }
    </style>
</head>
<body>
    @include('Admin.MesBons.partials.document', ['bon' => $bon, 'elements' => $elements])
</body>
</html>
