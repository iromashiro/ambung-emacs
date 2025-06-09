<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\Services\CategoryService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class HomeController extends Controller
{
    protected $productService;
    protected $categoryService;
    protected $storeService;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        StoreService $storeService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->storeService = $storeService;
    }

    /**
     * Display the home page with featured products, categories, and stores
     */
    public function index()
    {
        // Get featured products
        $featuredProducts = $this->productService->getFeaturedProducts(8);

        // Get popular categories
        $categories = $this->categoryService->getAllCategories();

        // Get featured stores
        $featuredStores = $this->storeService->getActiveStores(['limit' => 4]);

        // Get new arrivals
        $newArrivals = $this->productService->getActiveProducts([
            'limit' => 8,
            'sort' => 'created_at',
            'direction' => 'desc'
        ]);

        return view('welcome', compact(
            'featuredProducts',
            'categories',
            'featuredStores',
            'newArrivals'
        ));
    }

    /**
     * Display the about page
     */
    public function about()
    {
        return view('web.about');
    }

    /**
     * Display the contact page
     */
    public function contact()
    {
        return view('web.contact');
    }

    /**
     * Process the contact form submission
     */
    public function sendContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000'
        ]);

        // Send email
        Mail::to(config('mail.admin_address'))->send(new ContactFormMail(
            $request->name,
            $request->email,
            $request->subject,
            $request->message
        ));

        return redirect()->route('contact')->with('success', 'Your message has been sent successfully. We will get back to you soon.');
    }

    /**
     * Display the FAQ page
     */
    public function faq()
    {
        return view('web.faq');
    }

    /**
     * Display the terms and conditions page
     */
    public function terms()
    {
        return view('web.terms');
    }

    /**
     * Display the privacy policy page
     */
    public function privacy()
    {
        return view('web.privacy');
    }
}
