<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    // GET /api/items
    public function index(Request $request)
    {
        $query = Article::query()->where('status', 'published')->latest();

        if ($request->filled(['lat', 'lon', 'radius'])) {
            $lat = (float) $request->query('lat');
            $lon = (float) $request->query('lon');
            $radiusKm = (float) $request->query('radius');

            $kmPerDegLat = 111.32;
            $kmPerDegLon = max(cos(deg2rad(max(min($lat, 89.9), -89.9))) * 111.32, 0.00001);
            $dLat = $radiusKm / $kmPerDegLat;
            $dLon = $radiusKm / $kmPerDegLon;

            $query->whereBetween('lat', [$lat - $dLat, $lat + $dLat])
                  ->whereBetween('lon', [$lon - $dLon, $lon + $dLon]);
        }

        $perPage = (int) $request->query('per_page', 15);
        $items = $query->paginate($perPage)->appends($request->query());
        return ArticleResource::collection($items);
    }

    // GET /api/items/{article}
    public function show(Article $article)
    {
        return new ArticleResource($article);
    }

    // POST /api/items
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'description' => 'nullable|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'status' => 'in:draft,published',
        ]);

        $data['user_id'] = $request->user()->id ?? 1; // TODO: replace with auth user when ready
        $article = Article::create($data);
        return new ArticleResource($article);
    }
}
