<?php

namespace App\Command;

use App\Entity\Url;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'command:send-new-url',
    description: 'Send new url to service'
)]
class SendNewUrlCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
        private readonly string $serviceSendUrl,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $newUrls = $this->entityManager->getRepository(Url::class)->findBy(
                ['isSent' => false]
            );

        if (empty($newUrls)) {
            $io->success('Нет новых URL\'ов для отправки.');
            return Command::SUCCESS;
        }

        foreach ($newUrls as $newUrl) {
            $data = [
                'url' => $newUrl->getUrl(),
                'created_at' => $newUrl->getCreatedDate()->format('Y-m-d H:i:s')
            ];
        }
        try {
            $response = $this->httpClient->request('POST', $this->serviceSendUrl, [
                'json' => $data
            ]);

            if ($response->getStatusCode() === 200) {
                $this->findAndMarkAsSent($newUrls);
                $io->success('URL\'s успешно отправлеы: ');
                return Command::SUCCESS;
            } else {
                $io->error('Ошибка отправки URL\'');
            }
        } catch (\Exception $e) {
            $io->error('Произошла ошибка при отправке: ' . $e->getMessage());
            return Command::FAILURE;
        }
        return Command::FAILURE;
    }

    private function findAndMarkAsSent(array $urls): void
    {
        foreach ($urls as $url) {
            if ($url instanceof Url) {
                $url->setIsSent(true);
                $this->entityManager->persist($url);
            }
        }

        $this->entityManager->flush();
    }
}
