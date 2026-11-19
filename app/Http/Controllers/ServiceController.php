<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::published()->ordered()->get();

        return view('pages.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        abort_unless($service->is_published, 404);

        if ($service->slug === 'management-training') {
            return redirect()->to($service->publicUrl(), 301);
        }

        return $this->render($service);
    }

    public function managementTraining()
    {
        $service = Service::published()->where('slug', 'management-training')->firstOrFail();

        return $this->render($service);
    }

    private function render(Service $service)
    {
        $related = Service::published()
            ->where('id', '!=', $service->id)
            ->ordered()
            ->limit(4)
            ->get();

        return view('pages.services.show', compact('service', 'related'));
    }
}
