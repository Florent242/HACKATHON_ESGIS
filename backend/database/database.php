<?php

class JsonDatabase {
    private $file;
    private $data;

    public function __construct($file) {
        $this->file = $file;
        $this->load();
    }

    private function load() {
        if (!file_exists($this->file)) {
            $this->data = [
                'hackathons' => [],
                'equipes' => [],
                'participants' => [],
                'projets' => [],
                'evaluations' => [],
                'users' => [],
                'notifications' => [],
                'ressources' => [],
                'commentaires' => [],
                'equipe_membres' => []
            ];
            $this->save();
        } else {
            $content = file_get_contents($this->file);
            $this->data = json_decode($content, true);
            if ($this->data === null) {
                throw new Exception("Erreur lors de la lecture du fichier JSON");
            }
        }
    }

    private function save() {
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_put_contents($this->file, json_encode($this->data, JSON_PRETTY_PRINT))) {
            throw new Exception("Erreur lors de l'écriture dans le fichier JSON");
        }
    }

    public function getAll($table) {
        return $this->data[$table] ?? [];
    }

    public function find($table, $id) {
        foreach ($this->data[$table] ?? [] as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    public function create($table, $data) {
        if (!isset($this->data[$table])) {
            $this->data[$table] = [];
        }

        // Générer un nouvel ID
        $maxId = 0;
        foreach ($this->data[$table] as $item) {
            if ($item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        $data['id'] = $maxId + 1;
        
        // Ajouter les timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->data[$table][] = $data;
        $this->save();

        return $data['id'];
    }

    public function update($table, $id, $data) {
        foreach ($this->data[$table] ?? [] as $key => $item) {
            if ($item['id'] == $id) {
                $data['id'] = $id;
                $data['created_at'] = $item['created_at'];
                $data['updated_at'] = date('Y-m-d H:i:s');
                $this->data[$table][$key] = array_merge($item, $data);
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function delete($table, $id) {
        foreach ($this->data[$table] ?? [] as $key => $item) {
            if ($item['id'] == $id) {
                unset($this->data[$table][$key]);
                $this->data[$table] = array_values($this->data[$table]); // Réindexer le tableau
                $this->save();
                return true;
            }
        }
        return false;
    }

    public function query($table, $conditions = []) {
        $results = [];
        foreach ($this->data[$table] ?? [] as $item) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $item;
            }
        }
        return $results;
    }
}

try {
    $db = new JsonDatabase(__DIR__ . '/data.json');
    return $db;
} catch (Exception $e) {
    throw new Exception("Erreur de connexion à la base de données : " . $e->getMessage());
}
