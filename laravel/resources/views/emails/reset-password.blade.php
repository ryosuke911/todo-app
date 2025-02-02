<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>パスワードリセット</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2d3748; margin-bottom: 20px;">パスワードリセットのご案内</h2>
        
        <p>パスワードリセットのリクエストを受け付けました。以下のボタンをクリックして、新しいパスワードを設定してください。</p>
        
        <div style="margin: 30px 0;">
            <a href="{{ route('password.reset', ['token' => $token]) }}" 
               style="background-color: #4299e1; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                パスワードをリセット
            </a>
        </div>
        
        <p>このリンクは {{ config('auth.passwords.users.expire', 60) }} 分間有効です。</p>
        
        <p>パスワードリセットをリクエストしていない場合は、このメールを無視してください。</p>
        
        <hr style="margin: 30px 0; border: none; border-top: 1px solid #edf2f7;">
        
        <p style="color: #718096; font-size: 0.875rem;">
            このメールは自動送信されています。返信はできませんのでご了承ください。
        </p>
    </div>
</body>
</html> 