<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Address;
use App\Entity\Book;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\BookFormat;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer les utilisateurs
        $admin = $this->createUser($manager, 'admin@siyag.com', 'admin123', 'Admin', 'SIYAG', ['ROLE_ADMIN']);
        $client = $this->createUser($manager, 'client@test.com', 'client123', 'Jean', 'Dupont', ['ROLE_USER']);

        // Créer les auteurs
        $authors = $this->createAuthors($manager);

        // Créer les livres
        $books = $this->createBooks($manager, $authors);

        // Créer les commandes
        $this->createOrders($manager, $client, $books);

        $manager->flush();
    }

    private function createUser(ObjectManager $manager, string $email, string $password, string $firstname, string $lastname, array $roles): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setNumero('0600000000');
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $manager->persist($user);

        return $user;
    }

    private function createAuthors(ObjectManager $manager): array
    {
        $authorsData = [
            ['name' => 'Victor Hugo', 'bio' => 'Poète, dramaturge et prosateur romantique français.'],
            ['name' => 'Émile Zola', 'bio' => 'Écrivain et journaliste français, chef de file du naturalisme.'],
            ['name' => 'Marcel Proust', 'bio' => 'Écrivain français, auteur d\'À la recherche du temps perdu.'],
            ['name' => 'Gustave Flaubert', 'bio' => 'Romancier français du XIXe siècle.'],
            ['name' => 'Jules Verne', 'bio' => 'Écrivain français, pionnier de la science-fiction.'],
            ['name' => 'Alexandre Dumas', 'bio' => 'Écrivain français, auteur de romans d\'aventures.'],
            ['name' => 'Honoré de Balzac', 'bio' => 'Romancier français, auteur de La Comédie humaine.'],
            ['name' => 'Albert Camus', 'bio' => 'Écrivain et philosophe français, prix Nobel de littérature.'],
        ];

        $authors = [];
        foreach ($authorsData as $data) {
            $author = new Author();
            $author->setName($data['name']);
            $author->setBio($data['bio']);
            $manager->persist($author);
            $authors[$data['name']] = $author;
        }

        return $authors;
    }

    private function createBooks(ObjectManager $manager, array $authors): array
    {
        $books = [];
        $booksData = [
            ['title' => 'Les Misérables', 'isbn' => '978-2070409228', 'description' => 'Fresque sociale et historique du XIXe siècle.', 'price' => 1599, 'format' => BookFormat::PHYSICAL, 'stock' => 10, 'authors' => ['Victor Hugo']],
            ['title' => 'Notre-Dame de Paris', 'isbn' => '978-2070413089', 'description' => 'Roman historique au XVe siècle.', 'price' => 1299, 'format' => BookFormat::PHYSICAL, 'stock' => 15, 'authors' => ['Victor Hugo']],
            ['title' => 'Germinal', 'isbn' => '978-2070360338', 'description' => 'Roman sur la condition des mineurs.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Émile Zola']],
            ['title' => 'L\'Assommoir', 'isbn' => '978-2070368976', 'description' => 'Roman naturaliste sur l\'alcoolisme.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Émile Zola']],
            ['title' => 'Du côté de chez Swann', 'isbn' => '978-2070364701', 'description' => 'Premier tome d\'À la recherche du temps perdu.', 'price' => 1499, 'format' => BookFormat::PHYSICAL, 'stock' => 8, 'authors' => ['Marcel Proust']],
            ['title' => 'Madame Bovary', 'isbn' => '978-2070413119', 'description' => 'Roman sur l\'ennui d\'une femme de médecin.', 'price' => 1199, 'format' => BookFormat::PHYSICAL, 'stock' => 12, 'authors' => ['Gustave Flaubert']],
            ['title' => 'L\'Éducation sentimentale', 'isbn' => '978-2070368679', 'description' => 'La vie de la jeunesse parisienne.', 'price' => 1299, 'format' => BookFormat::PHYSICAL, 'stock' => 7, 'authors' => ['Gustave Flaubert']],
            ['title' => 'Vingt mille lieues sous les mers', 'isbn' => '978-2253006329', 'description' => 'Aventures sous-marines avec le Nautilus.', 'price' => 999, 'format' => BookFormat::PHYSICAL, 'stock' => 20, 'authors' => ['Jules Verne']],
            ['title' => 'Le Tour du monde en quatre-vingts jours', 'isbn' => '978-2253006336', 'description' => 'Le pari de Phileas Fogg.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Jules Verne']],
            ['title' => 'Les Trois Mousquetaires', 'isbn' => '978-2253098232', 'description' => 'Les aventures de d\'Artagnan.', 'price' => 1499, 'format' => BookFormat::PHYSICAL, 'stock' => 15, 'authors' => ['Alexandre Dumas']],
            ['title' => 'Le Comte de Monte-Cristo', 'isbn' => '978-2253098232', 'description' => 'Histoire de vengeance d\'Edmond Dantès.', 'price' => 1699, 'format' => BookFormat::PHYSICAL, 'stock' => 10, 'authors' => ['Alexandre Dumas']],
            ['title' => 'Le Père Goriot', 'isbn' => '978-2070369119', 'description' => 'Roman social sur un père dévoué.', 'price' => 1099, 'format' => BookFormat::PHYSICAL, 'stock' => 9, 'authors' => ['Honoré de Balzac']],
            ['title' => 'Eugénie Grandet', 'isbn' => '978-2253004905', 'description' => 'Roman sur l\'avarice.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Honoré de Balzac']],
            ['title' => 'L\'Étranger', 'isbn' => '978-2070360024', 'description' => 'Roman philosophique sur l\'absurde.', 'price' => 899, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Albert Camus']],
            ['title' => 'La Peste', 'isbn' => '978-2070360420', 'description' => 'Roman allégorique sur une épidémie.', 'price' => 999, 'format' => BookFormat::DIGITAL, 'stock' => null, 'authors' => ['Albert Camus']],
        ];

        foreach ($booksData as $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setIsbn($data['isbn']);
            $book->setDescription($data['description']);
            $book->setPriceCents($data['price']);
            $book->setFormat($data['format']);
            $book->setStock($data['stock']);
            $book->setIsActive(true);
            $book->setPublishedAt(new \DateTimeImmutable());

            foreach ($data['authors'] as $authorName) {
                if (isset($authors[$authorName])) {
                    $book->addAuthor($authors[$authorName]);
                }
            }

            $manager->persist($book);
            $books[$data['title']] = $book;
        }

        return $books;
    }

    private function createOrders(ObjectManager $manager, User $client, array $books): void
    {
        // Commande 1 - Payée (il y a 2 semaines)
        $order1 = new Order();
        $order1->setUser($client);
        $order1->setStatus(OrderStatus::PAID);
        $order1->setCreatedAt(new \DateTimeImmutable('-2 weeks'));
        $order1->setShippingAddress($this->createAddressForUser($manager, $client, '12 rue de la Paix', '75002', 'Paris'));
        $order1->setBillingAddress($order1->getShippingAddress());
        $order1->setBillingSameAsShipping(true);

        $item1 = new OrderItem();
        $item1->setBook($books['Les Misérables']);
        $item1->setTitleSnapshot('Les Misérables');
        $item1->setPriceSnapshot(1599);
        $item1->setQuantity(1);
        $order1->addOrderItem($item1);

        $item2 = new OrderItem();
        $item2->setBook($books['Germinal']);
        $item2->setTitleSnapshot('Germinal');
        $item2->setPriceSnapshot(999);
        $item2->setQuantity(2);
        $order1->addOrderItem($item2);

        $order1->calculateTotal();
        $manager->persist($order1);

        // Commande 2 - Payée (il y a 5 jours)
        $order2 = new Order();
        $order2->setUser($client);
        $order2->setStatus(OrderStatus::PAID);
        $order2->setCreatedAt(new \DateTimeImmutable('-5 days'));
        $order2->setShippingAddress($this->createAddressForUser($manager, $client, '18 avenue de France', '75013', 'Paris'));
        $order2->setBillingAddress($order2->getShippingAddress());
        $order2->setBillingSameAsShipping(true);

        $item3 = new OrderItem();
        $item3->setBook($books['Le Comte de Monte-Cristo']);
        $item3->setTitleSnapshot('Le Comte de Monte-Cristo');
        $item3->setPriceSnapshot(1699);
        $item3->setQuantity(1);
        $order2->addOrderItem($item3);

        $item4 = new OrderItem();
        $item4->setBook($books['Les Trois Mousquetaires']);
        $item4->setTitleSnapshot('Les Trois Mousquetaires');
        $item4->setPriceSnapshot(1499);
        $item4->setQuantity(1);
        $order2->addOrderItem($item4);

        $order2->calculateTotal();
        $manager->persist($order2);

        // Commande 3 - En attente (aujourd'hui)
        $order3 = new Order();
        $order3->setUser($client);
        $order3->setStatus(OrderStatus::PENDING);
        $order3->setCreatedAt(new \DateTimeImmutable());
        $order3->setShippingAddress($this->createAddressForUser($manager, $client, '8 boulevard Voltaire', '75011', 'Paris'));
        $order3->setBillingAddress($order3->getShippingAddress());
        $order3->setBillingSameAsShipping(true);

        $item5 = new OrderItem();
        $item5->setBook($books['L\'Étranger']);
        $item5->setTitleSnapshot('L\'Étranger');
        $item5->setPriceSnapshot(899);
        $item5->setQuantity(1);
        $order3->addOrderItem($item5);

        $order3->calculateTotal();
        $manager->persist($order3);

        // Commande 4 - Annulée (il y a 1 mois)
        $order4 = new Order();
        $order4->setUser($client);
        $order4->setStatus(OrderStatus::CANCELED);
        $order4->setCreatedAt(new \DateTimeImmutable('-1 month'));
        $order4->setShippingAddress($this->createAddressForUser($manager, $client, '3 rue des Lilas', '93100', 'Montreuil'));
        $order4->setBillingAddress($order4->getShippingAddress());
        $order4->setBillingSameAsShipping(true);

        $item6 = new OrderItem();
        $item6->setBook($books['Madame Bovary']);
        $item6->setTitleSnapshot('Madame Bovary');
        $item6->setPriceSnapshot(1199);
        $item6->setQuantity(3);
        $order4->addOrderItem($item6);

        $order4->calculateTotal();
        $manager->persist($order4);

        // Commande 5 - Remboursée (il y a 3 semaines)
        $order5 = new Order();
        $order5->setUser($client);
        $order5->setStatus(OrderStatus::REFUNDED);
        $order5->setCreatedAt(new \DateTimeImmutable('-3 weeks'));
        $order5->setShippingAddress($this->createAddressForUser($manager, $client, '45 rue de Lyon', '69002', 'Lyon'));
        $order5->setBillingAddress($order5->getShippingAddress());
        $order5->setBillingSameAsShipping(true);

        $item7 = new OrderItem();
        $item7->setBook($books['La Peste']);
        $item7->setTitleSnapshot('La Peste');
        $item7->setPriceSnapshot(999);
        $item7->setQuantity(2);
        $order5->addOrderItem($item7);

        $order5->calculateTotal();
        $manager->persist($order5);
    }

    private function createAddressForUser(ObjectManager $manager, User $user, string $street, string $postalCode, string $city): Address
    {
        $address = new Address();
        $address->setUser($user);
        $address->setFirstname($user->getFirstname());
        $address->setLastname($user->getLastname());
        $address->setNumero($user->getNumero());
        $address->setStreet($street);
        $address->setPostalCode($postalCode);
        $address->setCity($city);
        $address->setCountry('France');
        $manager->persist($address);

        return $address;
    }
}
