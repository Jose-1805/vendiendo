<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Producto;

class ImportController extends Controller {


    public function postStore()
    {
        Excel::load('books.xls', function($reader) {

            foreach ($reader->get() as $book) {
                Book::create([
                    'name' => $book->title,
                    'author' =>$book->author,
                    'year' =>$book->publication_year
                ]);
            }
        });
        return Book::all();
    }

    public function import()
    {
        Excel::load('books.xls', function($reader) {

            foreach ($reader->get() as $book) {
                Book::create([
                    'name' => $book->title,
                    'author' =>$book->author,
                    'year' =>$book->publication_year
                ]);
            }
        });
        return Book::all();
    }

}
