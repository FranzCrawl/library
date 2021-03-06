<?php

class Book extends Eloquent {

	protected $guarded = array('id');

	protected $fillable = array('name', 'pages', 'cover', 'date', 'quantity', 'ISBN');

	public static $rules = array(
        'name'	=> 'required',
        'pages' => 'required',
        'cover' => 'required',
        'date' 	=> 'required',
        'ISBN'	=> 'required',
        'quantity' =>'required|min:0|Integer'
  	);

	public function publisher()
	{
		return $this->belongsTo('Publisher');
	}

	public function category()
	{
		return $this->belongsTo('Category');
	}

	public function authors()
	{
		return $this->belongsToMany('Author');
	}

	public function translators()
	{
		return $this->belongsToMany('Translator');
	}

	public function publishers()
	{
		return $this->belongsToMany('Publisher');
	}

	public function usefuls()
	{
		return $this->belongsToMany('Useful');
	}

	public static function addSomething($someNames, $someClass)
	{
		$array = array();

		foreach(explode(', ', $someNames) as $some_name) {
			if($someObject = $someClass::whereName($some_name)->first()) {
				$array[] = $someObject->id;
			}
			else {
				$someObject = new $someClass;
				$someObject->name = $some_name;
				$someObject->save();
				$array[] = $someObject->id;
			}
		}
		return $array;
	}

	public static function saveBook()
	{
		$authors = Book::addSomething(Input::get('authors'), 'Author');
		if(Input::has('translators')) {
			$translators = Book::addSomething(Input::get('translators'), 'Translator');
		}
		$publishers = Book::addSomething(Input::get('publishers'), 'Publisher');
		$usefuls = Book::addSomething(Input::get('usefuls'), 'Useful');

		$category = Category::find(Input::get('category_id'));

		if(!$category) {
			return Redirect::back()->WithInput()->with('message', 'Такой категории не существует!');
		}

		$book = new Book(Input::all());
		$category->books()->save($book);
		$book->picture = Input::get('image');
		$book->authors()->sync($authors);
		if(Input::has('translators')) {
			$book->translators()->sync($translators);
		}
		$book->publishers()->sync($publishers);
		$book->usefuls()->sync($usefuls);

		foreach(Input::file('images') as $image) {
			$destinationPath = '/uploads/books_images/';
			$imagename = $destinationPath.str_random(12).'.jpg';
			$uploadflag = $image->move(public_path().$destinationPath, $imagename);

			if($uploadflag) {
				$uploadedimages[] = $imagename;
			}
		}

		$book->images = json_encode($uploadedimages);

		$book->save();
	}

	public static function updateBook($id)
	{
		$authors = Book::addSomething(Input::get('authors'), 'Author');
		if(Input::get('translators')) {
			$translators = Book::addSomething(Input::get('translators'), 'Translator');
		}
		$publishers = Book::addSomething(Input::get('publishers'), 'Publisher');
		$usefuls = Book::addSomething(Input::get('usefuls'), 'Useful');
		
		$category = Category::find(Input::get('category_id'));

		if(!$category) {
			return Redirect::back()->WithInput()->with('message', 'Такой категории не существует!');
		}

		$book = Book::find($id);
		$category->books()->save($book);
		$book->picture = Input::get('image');
		$book->quantity = Input::get('quantity');
		$book->authors()->detach($authors);
		$book->authors()->sync($authors);
		if(Input::has('translators')) {
			$book->translators()->detach($translators);
			$book->translators()->sync($translators);
		}
		$book->publishers()->detach($publishers);
		$book->publishers()->sync($publishers);
		$book->usefuls()->detach($usefuls);
		$book->usefuls()->sync($usefuls);

		if(Input::hasFile('images')) {
			if($book->images) {
				foreach(json_decode($book->images) as $image) {
					File::delete(public_path().$image);
				}
			}

			foreach(Input::file('images') as $image) {
				$destinationPath = '/uploads/books_images/';
				$imagename = $destinationPath.str_random(12).'.jpg';
				$uploadflag = $image->move(public_path().$destinationPath, $imagename);

				if($uploadflag) {
					$uploadedimages[] = $imagename;
				}
			}

			$book->images = json_encode($uploadedimages);
		}

		$book->save();
	}
}

