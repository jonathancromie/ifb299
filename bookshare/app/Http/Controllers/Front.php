<?php

namespace BookShare\Http\Controllers;

use Illuminate\Http\Request;

use BookShare\Http\Requests;
use BookShare\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;

use BookShare\Book;
use View;

use DB;

class Front extends Controller
{
    public function index() {
        return view('index', array('page' => 'index'));
    }

    /* 
     * REMOVE ALL THESE ONCE IN BOOKCONTROLER
     */

    public function share() {
        return view('share', array('page' => 'share'));
    }

    public function borrow() {
        $books = Book::all();
        return view('borrow', compact('books'));
    }

    public function search() {
        return view('search', array('page' => 'search'));
    }

    public function results() {
        // $books = Book::where('name', 'LIKE', Input::get('search'), 'AND', 'author', 'LIKE', Input::get('search'), 'AND', 'isbn', 'LIKE', Input::get('search'))->get();
        // $books = Book::whereRaw('name like ', Input::get('search'), array(25))->get();

        $search = Input::get('search');
        $query = '%'.$search.'%';

        // $books = Book::where('name', 'like', $query, 'and', 'author', 'like', $query, 'and', 'isbn', 'like', $query, 'and', 'faculty', 'like', $query)->get();

        $books = Book::where('name', 'like', $query)
                        ->orWhere('author', 'like', $query)
                        ->orWhere('isbn', 'like', $query)
                        ->orWhere('faculty', 'like', $query)->get();

        return View::make('results')->with('books', $books);
    }

    public function profile() {
        $contract = DB::table('contracts')
            ->join('students', 'contracts.sharer_id', '=', 'students.student_id')
            ->join('books', 'contracts.book_id', '=', 'books.book_id')
            ->select('students.first_name', 'students.last_name', 'books.name', 'contracts.due_date')
            ->get();


        return View::make('profile')->with('contract', $contract);
    }

    public function help() {
        return view('help', array('page' => 'help'));
    }

    public function faq() {
        return view('faq', array('page' => 'faq'));
    }

    public function terms() {
        return view('terms', array('page' => 'terms'));
    }

    // public function blog() {
    //     return 'blog page';
    // }

    // public function blog_post($id) {
    //     return 'blog post page';
    // }

    // public function contact_us() {
    //     return 'contact us page';
    // }
}
