<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            font-weight: bold;
        }
    </style>
</head>
<body>

<h2>Estudantes</h2>

<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>CPF</th>
            <th>Data de Nascimento</th>
            <th>Celular</th>
            <th>Endereço</th>
            <th>Instituição</th>
            <th>Status</th>
            <th>Linha</th>
            <th>Frequência</th>

        </tr>
    </thead>

    <tbody>
        @foreach ($estudantes as $estudante)
            <tr>
                <td>{{ $estudante['name'] }}</td>
                <td>{{ $estudante['email'] }}</td>
                <td>{{ $estudante['cpf'] }}</td>
                <td>{{ $estudante['birth_date'] }}</td>
                <td>{{ $estudante['phone'] }}</td>
                <td>{{ $estudante['address'] }}</td>
                <td>{{ $estudante['instituicao']['name'] }}</td>
                <td>{{ $estudante['status'] }}</td>
                <td>{{ $estudante['linha']['name'] ?? '-' }}</td>
                <td>EM DESENVOLVIMENTO</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>