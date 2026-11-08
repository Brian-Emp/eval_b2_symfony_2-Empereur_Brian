<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        // Catégorie passée dans l'URL : /?categorie=concert
        $categorie = $request->query->get('categorie');

        $events = $eventRepository->findUpcoming($categorie);

        return $this->render('home/index.html.twig', [
            'events' => $events,
            'categorie' => $categorie,
        ]);
    }
}
