<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NiveauxRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Module;
use Symfony\Component\HttpFoundation\Response;
class ClasseController extends AbstractController
{
    #[Route('/classe', name: 'app_classe')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ClasseController.php',
        ]);
    }
    #[Route('/classe/create', name: 'app_client_create')]
    public function create(): Response
    {
        $html = file_get_contents("../src/Views/Classe/create.html");
        return new Response($html);
    }
    #[Route('/classe/store', name: 'app_classe_store', methods: ['POST', 'GET'])]
    public function store(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['nom'], $data['niveaux_id'])) {
            return new JsonResponse(['message' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
        }
    
        $niveaux = $em->getRepository(Niveaux::class)->find($data['niveaux_id']);
    
        if (!$niveaux) {
            return new JsonResponse(['message' => 'Niveaux non trouvé'], Response::HTTP_NOT_FOUND);
        }
    
        $classe = new Classe();
        $classe->setNom($data['nom']);
        $classe->setNiveaux($niveaux);
    
        try {
            $em->persist($classe);
            $em->flush();
    
            return new JsonResponse([
                'message' => 'Classe créée avec succès',
                'classe' => [
                    'id' => $classe->getId(),
                    'nom' => $classe->getNom(),
                    'niveaux' => $classe->getNiveaux()->getNom() 
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Erreur lors de la création de la classe : ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/classe/liste', name: 'app_classe_liste', methods: ['GET'])]
    public function liste(EntityManagerInterface $em): Response
    {
        $classes = $em->getRepository(Classe::class)->findAll();

        return $this->render('classe/listeClasse.html', [
            'classes' => $classes,
        ]);
    }
    #[Route('/classe/{id}/cours', name: 'app_classe_cours', methods: ['GET'])]
    public function getCoursByClasse(int $id, EntityManagerInterface $em): Response
    {
        $classe = $em->getRepository(Classe::class)->find($id);

        if (!$classe) {
            return new Response("Classe non trouvée", Response::HTTP_NOT_FOUND);
        }

        $cours = [];
        foreach ($classe->getSessions() as $session) {
            $cours[] = $session->getCours();
        }

        return $this->render('classe/coursDeClasse.html.twig', [
            'classe' => $classe,
            'cours' => $cours,
        ]);
    }
    
    }
