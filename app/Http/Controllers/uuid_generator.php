<?php
namespace App\Http\Controllers;
use Ramsey\Uuid\Uuid;

class uuid_generator extends Controller
{
    public function index() {
        return Uuid::uuid4()->toString();
    }
}