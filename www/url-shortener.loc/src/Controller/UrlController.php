<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UrlController extends AbstractController
{
    public function __construct(
        private readonly string $urlLifeTime
    ) {
    }

    /**
     * @Route("/encode-url", name="encode_url")
     */
    public function encodeUrl(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $urlInput = $request->get('url');

        if (!$urlInput) {
            return $this->json(['error' => 'URL is required'], 400);
        }

        $constraint = new Assert\Url();

        $violations = $validator->validate($urlInput, $constraint);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return $this->json(['errors' => $errors], 400);
        }

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
        $url = $this->getUrlsByHash($request->get('hash'));

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
        $url = $this->getUrlsByHash($request->get('hash'));

        return $this->redirect($url->getUrl());
    }

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

    private function getUrlsByHash(string $hash): JsonResponse|Url
    {
        /** @var UrlRepository $urlRepository */
        $urlRepository = $this->getDoctrine()->getRepository(Url::class);
        $url = $urlRepository->findOneByHash($hash);

        if (empty ($url)) {
            return $this->json([
                'error' => 'Non-existent hash.'
            ]);
        }
        return $url;
    }
}
