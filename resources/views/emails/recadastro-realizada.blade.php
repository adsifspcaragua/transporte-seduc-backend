<!DOCTYPE html>

<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recadastro realizado</title>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: Arial, Helvetica, sans-serif; color: #333333;">

```
<table width="100%" cellpadding="0" cellspacing="0" style="padding: 40px 20px;">
    <tr>
        <td align="center">

            <table width="600" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 10px; overflow: hidden;">

                <tr>
                    <td style="padding: 25px; text-align: center; background-color: #1f4e79;">
                        <h1 style="margin: 0; color: #ffffff; font-size: 24px;">
                            Recadastro realizado
                        </h1>
                    </td>
                </tr>

                <tr>
                    <td style="padding: 35px;">

                        <h2 style="margin-top: 0;">
                            Olá, {{ $dados['nome'] }}!
                        </h2>

                        <p style="font-size: 16px; line-height: 1.6;">
                            Seu recadastro foi realizado com sucesso.
                        </p>

                        <p style="font-size: 16px; line-height: 1.6;">
                            As informações atualizadas foram recebidas pelo sistema e serão analisadas pela equipe responsável.
                        </p>

                        <p style="font-size: 16px; line-height: 1.6;">
                            Você receberá uma nova notificação após a conclusão da análise.
                        </p>

                        <p style="margin-top: 30px; font-size: 14px; color: #666666;">
                            Este é um e-mail automático. Não responda a esta mensagem.
                        </p>

                    </td>
                </tr>

                <tr>
                    <td style="padding: 20px; text-align: center; background-color: #f4f6f8; color: #777777; font-size: 12px;">
                        {{ config('app.name') }}
                    </td>
                </tr>

            </table>

        </td>
    </tr>
</table>
```

</body>
</html>
