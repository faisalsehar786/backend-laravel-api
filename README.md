## Laravel Blog Rest API
This is a blog rest API project including blog features like category,article,author,comment, search , authentication using PHP Laravel framework & MySQL database.

## How to use ?
Follow these steps to get this project live

```
git clone https://github.com/username/laravel-blog-api.git

cd laravel-blog-api
composer install

All collection of end points in the folder postmanendpoints

the data base for test the file in floder   mysqldatabase

```
## Configure your .env file

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_name
DB_USERNAME=db_username
DB_PASSWORD=db_password

```  

## Final steps

```
php artisan migrate
php artisan key:generate
http://localhost:8000

api end point
http://localhost:8000/api

```
## Features
    1. Category (Add/Update/Remove/Search)
    2. Article (Add/Update/Remove/Search/Article wise comment)
    3. Author (Register/Login/Logout)
    4. Comment (Add/Update/Remove)

## Authorization
    1. Category -> Needs to be authenticated for add/update/remove
    2. Article -> Needs to be authenticated for add/update/remove
    3. Author -> Needs to be authenticated for details/logout
    4. Comment -> Needs to be authenticated for add/update/remove
    
## Endpoints
    * Category
        1 ) Add -> /api/category/store 
        2 ) Update -> /api/category/{id}/update 
        3 ) Remove -> /api/category/{id}/remove 
        4 ) Show -> /api/category/{id}/show 
        5 ) All -> /api/categories
        6 ) Search -> /api/category/{keyword}/search
        
    2. Article
        1 ) Add -> /api/article/store 
        2 ) Update -> /api/article/{id}/update 
        3 ) Remove -> /api/article/{id}/remove 
        4 ) Show -> /api/article/{id}/show 
        5 ) All -> /api/articles
        6 ) Search -> /api/article/search?keyword=test&date=2023-07-01&order=desc    its local db
         7)  Search->/api/article/search_article_from_multi_sources?keyword=tesla&date_from=2014-01-01&source=&category=&date_to=2014-01-01&page=1   its from private apis 
        
    3. Author
        1 ) Register -> /api/register 
        2 ) Login -> /api/login
        3 ) Logout -> /api/logout
        
    4. Comment
        1 ) Add -> /api/comment/store 
        2 ) Update -> /api/comment/{id}/update 
        3 ) Remove -> /api/comment/{id}/remove 
        4 ) Show -> /api/comment/{id}/show 
        5 ) All -> /api/comment

You can use Faker for generating random dummy data using the factories defined in this project.Just follow these steps
```
php artisan tinker
factory(App\ModelName::class,number_of_column)->create()
```


‘headers’ => [
    ‘Accept’ => ‘application/json’,
    ‘Authorization’ => ‘Bearer ‘.$accessToken,
]
```

## Thanks