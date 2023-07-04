<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Response;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Resources\CommentResource;
use Illuminate\Validation\Rule;
use App\Models\Article;
use App\Models\Comment;
use Illuminate\Support\Facades\Http;
use Auth;
use Illuminate\Support\Str;
class ArticleController extends Controller {
    // show all the article
    public function index() {
        return ArticleResource::collection(Article::where("author_id", Auth::user()->id)->orderBy("id", "DESC")->paginate(10));
    }
    // check title validation
    public function checkTitle(Request $request) {
        $validators = Validator::make($request->all(), ["title" => "required"]);
        return Response::json(["errors" => $validators->getMessageBag()->toArray(), ]);
    }
    // check category validation
    public function checkCategory(Request $request) {
        $validators = Validator::make($request->all(), ["category" => "required", ]);
        return Response::json(["errors" => $validators->getMessageBag()->toArray(), ]);
    }
    // check body validation
    public function checkBody(Request $request) {
        $validators = Validator::make($request->all(), ["body" => "required"]);
        return Response::json(["errors" => $validators->getMessageBag()->toArray(), ]);
    }
    // store new article into the database
    public function store(Request $request) {
        $validators = Validator::make($request->all(), ["title" => "required", "category" => "required", "body" => "required", ]);
        if ($validators->fails()) {
            return Response::json(["errors" => $validators->getMessageBag()->toArray(), ]);
        } else {
            $article = new Article();
            $article->title = $request->title;
            $article->author_id = Auth::user()->id;
            $article->category_id = $request->category;
            $article->body = $request->body;
            if ($request->file("image") == null) {
                $article->image = "placeholder.png";
            } else {
                $filename = Str::random(20) . "." . $request->file("image")->getClientOriginalExtension();
                $article->image = $filename;
                $request->image->move(public_path("images"), $filename);
            }
            $article->save();
            return Response::json(["success" => "Article created successfully !", ]);
        }
    }
    // show a specific article by id
    public function show($id) {
        if (Article::where("id", $id)->first()) {
            return new ArticleResource(Article::findOrFail($id));
        } else {
            return Response::json(["error" => "Article not found!"]);
        }
    }
    // update article using id
    public function update(Request $request) {
        $validators = Validator::make($request->all(), ["title" => "required", "category" => "required", "body" => "required", ]);
        if ($validators->fails()) {
            return Response::json(["errors" => $validators->getMessageBag()->toArray(), ]);
        } else {
            $article = Article::where("id", $request->id)->where("author_id", Auth::user()->id)->first();
            if ($article) {
                $article->title = $request->title;
                $article->author_id = Auth::user()->id;
                $article->category_id = $request->category;
                $article->body = $request->body;
                if ($request->file("image") == null) {
                    $article->image = "placeholder.png";
                } else {
                    $filename = Str::random(20) . "." . $request->file("image")->getClientOriginalExtension();
                    $article->image = $filename;
                    $request->image->move(public_path("images"), $filename);
                }
                $article->save();
                return Response::json(["success" => "Article updated successfully !", ]);
            } else {
                return Response::json(["error" => "Article not found !"]);
            }
        }
    }
    // remove article using id
    public function remove(Request $request) {
        try {
            $article = Article::where("id", $request->id)->where("author_id", Auth::user()->id)->first();
            if ($article) {
                $article->delete();
                return Response::json(["success" => "Article removed successfully !", ]);
            } else {
                return Response::json(["error" => "Article not found!"]);
            }
        }
        catch(\Illuminate\Database\QueryException $exception) {
            return Response::json(["error" => 'Article belongs to comment.So you cann\'t delete this article!', ]);
        }
    }
    // search article by keyword
    public function searchArticle(Request $request) {
        $articles = Article::where("title", "LIKE", "%" . $request->query("keyword") . "%");
        if ($request->query("date")) {
            $articles->whereDate("created_at", $request->query("date"));
        }
        if ($request->query("order")) {
            $articles->orderBy("created_at", $request->query("order"));
        }
        return Response::json($articles->paginate(10));
    }
    // search article by keyword
    public function searchArticleFromMultiSources(Request $request) {
        try {
            $firstArray = [];
            $secondArray = [];
            $thirdArray = [];
            $totalRes = 0;
            $page = $request->query("page") ? $request->query("page") : 1;
            if ($request->query("source") == 'the-guardian') {
                $responseReq1 = Http::get(env("GUARDIANAPIKEY_Url") . "?q=" . $request->query("keyword") . "&page=" . $page . "&page-size=10&api-key=" . env("GUARDIANAPIKEY") . "");
                if ($responseReq1->successful()) {
                    // dd($responseReq1['response']);
                    $firstArray = $responseReq1["response"]["results"];
                    $totalRes = $responseReq1["response"]["total"];
                }
            } elseif ($request->query("source") == 'new-york-times') {
                $responseReq3 = Http::get(env("NWEYORKTIME_Url") . '?page=1&q=' . $request->query("keyword") . '&sort=newest&api-key=' . env("NEWYORKTIMEAPIKEY") . '');
                if ($responseReq3->successful()) {
                    $thirdArray = $responseReq3["response"]['docs'];
                    $totalRes = $totalRes + 10;
                }
            } else {
                $responseReq2 = Http::get(env("NEWSAPI_URL") . "?q=" . $request->query("keyword") . "&sources=" . $request->query("source") . "&from=" . $request->query("date_from") . "&to=" . $request->query("date_to") . " || category=" . $request->query("category") . "&page=" . $page . "&pageSize=10&apiKey=" . env("NEWSAPIKEY") . "");
                if ($responseReq2->successful()) {
                    $secondArray = $responseReq2["articles"];
                    $totalRes = $totalRes + $responseReq2["totalResults"];
                }
            }
            $composeArray1 = [];
            $composeArray2 = [];
            $composeArray3 = [];
            foreach ($firstArray as $articleData1) {
                $composeArray1[] = ["title" => $articleData1["webTitle"], "content" => "", 'description' => '', 'author' => $articleData1["sectionName"], "thumbnail" => "blogthumb.png", "source" => $articleData1["sectionName"], "category" => $articleData1["pillarName"], "weburl" => $articleData1["webUrl"], "apiurl" => $articleData1["apiUrl"], "publish_date" => $articleData1["webPublicationDate"], ];
            }
            foreach ($secondArray as $articleData2) {
                $composeArray2[] = ["title" => $articleData2["title"], "content" => $articleData2["content"], 'description' => $articleData2["description"], 'author' => $articleData2["author"], "thumbnail" => $articleData2["urlToImage"], "source" => $articleData2["source"]["name"], "category" => $request->query("category") ? $request->query("category") : null, "weburl" => $articleData2["url"], "apiurl" => "", "publish_date" => $articleData2["publishedAt"], ];
            }
            foreach ($thirdArray as $articleData3) {
                $composeArray3[] = ["title" => $articleData3["abstract"], "content" => $articleData3["lead_paragraph"], 'description' => $articleData3["lead_paragraph"], 'author' => 'uknown', "thumbnail" => "blogthumb.png", "source" => $articleData3["source"], "category" => $request->query("category") ? $request->query("category") : null, "weburl" => $articleData3["web_url"], "apiurl" => "", "publish_date" => $articleData3["pub_date"], ];
            }
            $data = array_merge($composeArray2, $composeArray1, $composeArray3);
            return Response::json(["page" => $page, "totalrecords" => $totalRes, "articles" => $data, ]);
        }
        catch(\Throwable $th) {
            return Response::json(["articles" => []]);
        }
    }
    // fetch comments for a specific article
    public function comments($id) {
        if (Article::where("id", $id)->first()) {
            return CommentResource::collection(Comment::where("article_id", $id)->get());
        } else {
            return Response::json(["error" => "Article not found!"]);
        }
    }
}
