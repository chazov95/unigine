<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    public function __construct(
        private readonly string $urlLifeTime
    )
    {
    }

    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request): JsonResponse
    {
        $newUrl = new Url();
        $newUrl->setUrl($request->get('url'));

        $entityManager = $this->getDoctrine()->getManager();
        $url = $entityManager->getRepository(Url::class)->findOneBy(['url' => $newUrl->getUrl()]);

        if ($url === null) {
            $entityManager->persist($newUrl);
            $entityManager->flush();
            $url = $newUrl;
        }

        return $this->json(['hash' => $url->getHash()]);
    }

    /**
     * @Route("/decode-url", name="decode_url")
     * @throws \Exception
     */
    public function decodeUrl(Request $request): JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));

        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        if ($this->checkUrlLifeTime($url)) {
            throw new \Exception('Url has expired.');
        }

        return $this->json([
            'url' => $url->getUrl()
        ]);
    }

    /**
     * @Route("/redirect-url", name="redirect_url")
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function redirectUrl(Request $request): RedirectResponse|JsonResponse
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($request->get('hash'));
        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        return $this->redirect($url->getUrl());
    }

    //TODO перенести в UrlService
    /**
     * @param Url $url
     * @return bool
     *  true - урл не действителен
     *  false - урл действителен
     */
    private function checkUrlLifeTime(Url $url): bool
    {
        return $url->getCreatedDate()->modify("+ $this->urlLifeTime") < new \DateTime();

    }
}
