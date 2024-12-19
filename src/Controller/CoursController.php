<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CoursController extends AbstractController
{
    #[Route('/cours', name: 'app_cours')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CoursController.php',
        ]);
    }
    #[Route('/cours/create', name: 'app_cours_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $cours = new Cours();
            $cours->setNom($data['nom']);
            $cours->setModule($data['module']);

            $professeur = $em->getRepository(Prof::class)->find($data['professeur']);
            if (!$professeur) {
                return new Response("Professeur introuvable", Response::HTTP_BAD_REQUEST);
            }
            $cours->setProfesseur($professeur);

            try {
                $em->persist($cours);
                $em->flush();

                return $this->redirectToRoute('app_cours_liste');
            } catch (\Exception $e) {
                return new Response("Erreur lors de la création du cours : " . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
    #[Route('/cours/liste', name: 'app_cours_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $em): Response
    {
        $cours = $em->getRepository(Cours::class)->findAll();

        return $this->render('cours/listeCours.html', [
            'cours' => $cours,
        ]);
    }
}
