<?php
    interface ModeleDao {
        public function getAll(): array;
        public function getById(int $id): ?object;
        public function add(object $obj):bool;
        public function update(object $obj):bool;
        public function delete(object $obj):bool;
    }
?>