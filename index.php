<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/Task.php';
require_once __DIR__ . '/TaskRepository.php';
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
$repo = new TaskRepository(__DIR__ . '/tasks.json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
        if ($title !== '') {
            $repo->add($title, $deadline);
        }
    } elseif ($action === 'complete') {
        $repo->complete((int)($_POST['id'] ?? 0));
    } elseif ($action === 'delete') {
        $repo->delete((int)($_POST['id'] ?? 0));
    }

    header('Location: index.php');
    exit;
}
$tasks = $repo->findAll();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Менеджер завдань</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f9f9f9; }
        .box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .task { padding: 10px; border-bottom: 1px solid #eee; margin-bottom: 10px; }
        .done { text-decoration: line-through; color: gray; }
        .stats { background: #eef; padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    </style>
</head>
<body>
<div class="box">
    <p>Привіт, <b><?= htmlspecialchars($_SESSION['user_name'] ?? 'Користувач') ?></b>! <a href="logout.php">Вийти</a></p>
    
    <h2>Менеджер завдань (ООП)</h2>

    <div class="stats">
        Всього завдань: <?= count($tasks) ?> | 
        Виконано: <?= $repo->countCompleted() ?> | 
        Прострочено: <?= $repo->countOverdue() ?>
    </div>

    <form method="POST" style="margin-bottom: 20px;">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="Назва завдання..." required>
        <input type="date" name="deadline">
        <button type="submit">Додати</button>
    </form>

    <div>
        <?php if (empty($tasks)): ?>
            <p>Список завдань порожній.</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="task">
                    <span class="<?= $task->isCompleted() ? 'done' : '' ?>">
                        <b><?= htmlspecialchars($task->getTitle())?></b> 
                        (Дедлайн: <?= $task->getDeadline() ?? 'немає' ?>) — 
                        <em><?= $task->getStatusLabel() ?></em>
                    </span>
                    
                    <div style="margin-top: 5px;">
                        <?php if (!$task->isCompleted()): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="complete">
                                <input type="hidden" name="id" value="<?= $task->getId() ?>">
                                <button type="submit">Виконати</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $task->getId() ?>">
                            <button type="submit" style="color:red;">Видалити</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>