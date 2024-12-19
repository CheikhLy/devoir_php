<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/SessionController.php',
        ]);
    }
    #[Route('/session/create', name: 'app_session_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em, ClasseRepository $classeRepository): Response
    {
        $classes = $classeRepository->findAll();

        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $session = new Session();
            $session->setNom($data['nom']);
            $session->setClasse($em->getRepository(Classe::class)->find($data['classe']));
            $session->setDateDebut(new \DateTime($data['date_debut']));
            $session->setDateFin(new \DateTime($data['date_fin']));

            try {
                $em->persist($session);
                $em->flush();

                return $this->redirectToRoute('app_session_list');
            } catch (\Exception $e) {
                return $this->json(['message' => 'Erreur lors de la création de la session: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return $this->render('session/create.html', [
            'classes' => $classes,
        ]);
    }

#[Route('/sessions', name: 'app_session_list', methods: ['GET'])]
public function list(EntityManagerInterface $em): Response
{
    $sessions = $em->getRepository(Session::class)->findAll();

    return $this->render('session/list.html', [
        'sessions' => $sessions,
    ]);
}

}
