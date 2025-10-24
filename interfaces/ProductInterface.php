<?php
interface ProductInterface {
    public function create($data);
    public function read();
    public function update($id, $data);
    public function delete($id);
    public function search($keyword);
    public function getCategories();
    public function getProductById($id);
}
?>