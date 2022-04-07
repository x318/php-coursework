<?php

class Film
{
    public string $title;
    public string $description;
    public string $price;
    public string $img;
    public string $category;

    public function __construct(string $title, string $description, string $price, string $img = '', string $category)
    {
        $this->title = $title;
        $this->description = $description;
        $this->price = $price;
        $this->img = $img;
        $this->category = $category;
    }
}
