//这个是用户查询方便查询网站所有用户，以便用户添加别人的好友
<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class UserService
{
    private const USERS_FILE = 'data/users.txt';
    
    public function getAllUsers(): array
    {
        if (!file_exists(self::USERS_FILE)) {
            throw new RuntimeException('用户数据文件不存在');
        }

        $lines = file(self::USERS_FILE, FILE_IGNORE_NEW_LINES);
        $users = [];
        
        foreach ($lines as $i => $line) {
            $parts = explode('|', $line);
            $users[] = [
                'id' => $i + 1,
                'username' => $parts[0] ?? '空'
            ];
        }
        
        return $users;
    }
    
    public function getUserDirectories(): array
    {
        $dirs = glob('data/*', GLOB_ONLYDIR);
        return array_map('basename', $dirs);
    }
}

// 响应处理
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>好友查询系统</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media (max-width: 768px) {
            .user-card {
                margin-bottom: 1rem;
            }
            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">好友查询系统</h1>
        
        <div class="card mb-4">
            <div class="card-header">
                <h2>当前所有注册用户</h2>
            </div>
            <div class="card-body">
                <?php
                try {
                    $userService = new UserService();
                    $users = $userService->getAllUsers();
                    
                    if (empty($users)) {
                        echo '<div class="alert alert-info">没有找到任何用户</div>';
                    } else {
                        echo '<div class="row">';
                        foreach ($users as $user) {
                            echo '<div class="col-md-6 col-lg-4 user-card">';
                            echo '<div class="card">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . htmlspecialchars($user['username']) . '</h5>';
                            echo '<p class="card-text">用户ID: ' . $user['id'] . '</p>';
                            echo '</div></div></div>';
                        }
                        echo '</div>';
                    }
                } catch (RuntimeException $e) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>用户目录</h2>
            </div>
            <div class="card-body">
                <?php
                try {
                    $directories = $userService->getUserDirectories();
                    
                    if (empty($directories)) {
                        echo '<div class="alert alert-info">没有找到任何用户目录</div>';
                    } else {
                        echo '<ul class="list-group">';
                        foreach ($directories as $dir) {
                            echo '<li class="list-group-item">' . htmlspecialchars($dir) . '</li>';
                        }
                        echo '</ul>';
                    }
                } catch (RuntimeException $e) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
