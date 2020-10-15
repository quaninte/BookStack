<?php


namespace BookStack\Http\Controllers\Api;


use BookStack\Entities\Book;
use BookStack\Entities\Page;
use BookStack\Entities\Repos\PageRepo;
use BookStack\Facades\Activity;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PageApiController extends ApiController
{

    /**
     * @var PageRepo $pageRepo
     */
    protected $pageRepo;

    protected $rules = [
        'create' => [
            'name' => 'required|string|max:255',
            'html' => 'string',
        ],
        'update' => [
            'name' => 'string|min:1|max:255',
            'html' => 'string',
        ],
    ];

    /**
     * PageApiController constructor.
     */
    public function __construct(PageRepo $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    /**
     * Get a listing of pages from a book visible to the user.
     * @param $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function list($bookId)
    {
        $pages = Page::visible()->where('book_id', '=', $bookId)
            ->where('draft', '=', false);

        return $this->apiListingResponse($pages, [
            'id', 'name', 'slug', 'created_at', 'updated_at', 'created_by', 'updated_by',
        ]);
    }

    /**
     * Create a new page in a book.
     * @throws ValidationException
     */
    public function create($bookId, Request $request)
    {
        $book = Book::visible()->where('id', '=', $bookId)->firstOrFail();
        $this->checkOwnablePermission('page-create', $book);
        $this->checkPermission('page-create-all');
        $requestData = $this->validate($request, $this->rules['create']);

        // Create draft
        $draft = $this->pageRepo->getNewDraftPage($book);
        $page = $this->pageRepo->publishDraft($draft, $request->all());

        Activity::add($page, 'page_create', $page->id);

        return response()->json($page);
    }

}
