<?php
declare(strict_types=1);
require_once __DIR__ . '/Task.php';
class TaskRepository
{
    private array $tasks = [];
    private string $file;

    public function __construct(string $storageFile) {
        $this->file = $storageFile;
        $this->load();
    }
    public function findAll(): array {
        return $this->tasks;
    }
    public function add(string $title, ?string $deadline = null): void {
        $id = empty($this->tasks) ? 1 : max(array_map(fn($t) => $t->getId(), $this->tasks)) + 1;
        $task = new Task($id, $title, false, $deadline);
        $this->tasks[] = $task;
        $this->save();
    }
    public function complete(int $id): void {
        foreach ($this->tasks as $task) {
            if ($task->getId() === $id) {
                $task->complete();
                break;
            }
        }
        $this->save();
    }
    public function delete(int $id): void {
        foreach ($this->tasks as $index => $task) {
            if ($task->getId() === $id) {
                unset($this->tasks[$index]);
                $this->tasks = array_values($this->tasks);
                break;
            }
        }
        $this->save();
    }
    public function countCompleted(): int {
        return count(array_filter($this->tasks, fn($t) => $t->isCompleted()));
    }
    public function countOverdue(): int {
        return count(array_filter($this->tasks, fn($t) => $t->isOverdue()));
    }
    private function load(): void {
        if (!file_exists($this->file)) {
            $this->tasks = [];
            return;
        }
        $json = file_get_contents($this->file);
        $data = json_decode($json, true);
        
        $this->tasks = [];
        if (is_array($data)) {
            foreach ($data as $item) {
                $this->tasks[] = Task::fromArray($item);
            }
        }
    }
    private function save(): void {
        $data = [];
        foreach ($this->tasks as $task) {
            $data[] = $task->toArray();
        }
        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}