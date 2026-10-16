<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Politique de confidentialité et conditions d'utilisation – Mobitrace</title>
    <meta name="description" content="Politique de confidentialité et conditions générales d'utilisation de l'application Mobitrace.">
    <style>
        :root {
            --fond: #ffffff;
            --texte: #1d2521;
            --discret: #5b6760;
            --trait: #dfe5e1;
            --accent: #1f7a4d;
            --surligne: #fff3c4;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --fond: #121715;
                --texte: #e7ece9;
                --discret: #9aa8a0;
                --trait: #2a332f;
                --accent: #5fc58f;
                --surligne: #4a4116;
            }
        }
        * { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--fond);
            color: var(--texte);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 1rem;
            line-height: 1.65;
        }
        main { max-width: 44rem; margin: 0 auto; padding: 1.5rem 1.25rem 4rem; }
        h1 { font-size: 1.6rem; line-height: 1.25; margin: 0 0 .5rem; }
        h2 { font-size: 1.2rem; margin: 2.25rem 0 .5rem; line-height: 1.3; }
        h3 { font-size: 1.02rem; margin: 1.5rem 0 .25rem; }
        h2, h3 { scroll-margin-top: 1rem; }
        p { margin: .6rem 0; }
        ul, ol { padding-left: 1.3rem; margin: .6rem 0; }
        li { margin: .3rem 0; }
        a { color: var(--accent); }
        .meta { color: var(--discret); margin: 0 0 1.25rem; }
        .sommaire { border-top: 1px solid var(--trait); border-bottom: 1px solid var(--trait); padding: .75rem 0; margin: 1.25rem 0 0; }
        .sommaire a { margin-right: 1.25rem; font-weight: 600; }
        .partie { margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid var(--trait); font-size: 1.35rem; }
        mark { background: var(--surligne); color: inherit; padding: 0 .2em; border-radius: 2px; }
        footer { margin-top: 3rem; padding-top: 1rem; border-top: 1px solid var(--trait); color: var(--discret); font-size: .9rem; }
    </style>
</head>
<body>
<main>

    <h1>Politique de confidentialité et conditions d'utilisation</h1>
    <p class="meta"><strong>Mobitrace</strong> – Application de gestion des transactions Mobile Money<br>
        Version 1.0 – Dernière mise à jour : 16 octobre 2026</p>

    <nav class="sommaire" aria-label="Sommaire">
        <a href="#confidentialite">Politique de confidentialité</a>
        <a href="#conditions">Conditions d'utilisation</a>
    </nav>

    {{-- ===================== PARTIE 1 ===================== --}}
    <h1 id="confidentialite" class="partie">Politique de confidentialité</h1>

    <h2>1. Introduction</h2>
    <p>La présente politique explique quelles données personnelles sont collectées par l'application <strong>Mobitrace</strong> (ci-après « l'Application »), pourquoi elles le sont, comment elles sont protégées et quels sont vos droits.</p>
    <p>L'Application est destinée aux agents Mobile Money. Elle remplace le cahier papier traditionnel : elle permet d'enregistrer les dépôts et retraits, de conserver les informations des clients, de retrouver l'historique des opérations et de garder une trace des modifications effectuées.</p>
    <p>En créant un compte et en utilisant l'Application, vous reconnaissez avoir pris connaissance de la présente politique.</p>

    <h2>2. Qui est responsable de vos données ?</h2>
    <p>La protection des données concerne deux catégories de personnes. Il est important de bien distinguer les rôles :</p>
    <ul>
        <li><strong>L'agent (vous, utilisateur de l'Application)</strong> : pour les données de ses propres clients (nom, téléphone, pièce d'identité, transactions), l'agent est le <strong>responsable du traitement</strong>. C'est lui qui décide d'enregistrer ces informations et qui doit respecter ses obligations vis-à-vis de ses clients.</li>
        <li><strong>L'éditeur de l'Application</strong> : <strong>l'équipe de développement Mobitrace</strong>, Ouagadougou (Burkina Faso). Il est responsable du traitement pour les données du <strong>compte de l'agent</strong> (nom, numéro de téléphone, code d'accès, adresse e-mail, journaux de connexion) et agit comme <strong>sous-traitant</strong> pour les données des clients de l'agent : il les héberge et les traite uniquement pour faire fonctionner l'Application, selon les instructions de l'agent.</li>
    </ul>
    <p><strong>Contact pour toute question relative aux données personnelles :</strong> <a href="mailto:mobitrace11@gmail.com">mobitrace11@gmail.com</a>.</p>

    <h2>3. Quelles données collectons-nous ?</h2>

    <h3>3.1 Données du compte de l'agent</h3>
    <ul>
        <li>nom ;</li>
        <li>numéro de téléphone (identifiant de connexion) et code agent ;</li>
        <li>code d'accès à 8 chiffres (stocké sous forme hachée, jamais en clair) ;</li>
        <li>adresse e-mail (utilisée pour la réinitialisation du code d'accès) ;</li>
        <li>photo de profil, si vous en ajoutez une ;</li>
        <li>localisation de votre point de service, saisie par vos soins (il ne s'agit pas d'une géolocalisation GPS) ;</li>
        <li>dates de création et de modification du compte ;</li>
        <li>données techniques de connexion (jetons d'authentification, journaux de sécurité).</li>
    </ul>

    <h3>3.2 Données des clients enregistrés par l'agent</h3>
    <ul>
        <li>numéro de téléphone ;</li>
        <li>nom et prénoms ;</li>
        <li>date de naissance (facultative) ;</li>
        <li>type de pièce d'identité, numéro de la pièce, date de délivrance et date d'expiration (facultatifs, pouvant être complétés ultérieurement).</li>
    </ul>
    <p>Les informations de pièces d'identité sont des données particulièrement sensibles. Elles ne doivent être enregistrées que lorsque cela est nécessaire à l'activité de l'agent et conformément aux exigences de son opérateur Mobile Money. Le numéro de la pièce est chiffré dans la base de données, et <strong>aucune image de la pièce n'est conservée</strong> (voir la section 3.5).</p>

    <h3>3.3 Données des transactions et de la caisse</h3>
    <ul>
        <li>type d'opération (dépôt ou retrait) ;</li>
        <li>service utilisé (Orange Money, Moov Money, Telecel Money, ou autre service ajouté ultérieurement) ;</li>
        <li>montant ;</li>
        <li>référence de la transaction ;</li>
        <li>note facultative ;</li>
        <li>statut (enregistrée, modifiée, annulée) ;</li>
        <li>confirmation du client : récapitulatif de l'opération confirmée et horodatage de la confirmation ;</li>
        <li>date et heure d'enregistrement et de dernière modification ;</li>
        <li>données de caisse : soldes d'ouverture et de clôture par service (float et espèces), ajustements de caisse (recharges, corrections) avec leur motif, écarts constatés et leur justification.</li>
    </ul>

    <h3>3.4 Historique des actions (traçabilité)</h3>
    <p>Pour chaque création, modification ou annulation d'une transaction, l'Application conserve : la date et l'heure, l'utilisateur ayant effectué l'action, les anciennes et nouvelles informations, et le motif lorsqu'il est demandé.</p>

    <h3>3.5 Autorisations demandées sur votre téléphone</h3>
    <ul>
        <li><strong>Caméra</strong> : elle sert uniquement à scanner la carte d'identité (CNIB) du client pour pré-remplir sa fiche (nom, prénoms, numéro de pièce, dates de délivrance et d'expiration). L'analyse est réalisée sur votre téléphone : l'image de la carte n'est pas envoyée à nos serveurs et n'est pas conservée après l'extraction. Vous pouvez toujours saisir ces informations manuellement.</li>
        <li><strong>Stockage</strong> : l'accès au stockage de l'appareil permet d'enregistrer les fichiers générés par l'Application, notamment l'export PDF de vos transactions, ainsi que les fichiers temporaires nécessaires au scan.</li>
    </ul>

    <h3>3.6 Ce que l'Application ne collecte pas</h3>
    <p>Dans sa version actuelle, l'Application :</p>
    <ul>
        <li><strong>n'accède pas</strong> à vos SMS, à vos contacts ni à votre localisation GPS ;</li>
        <li><strong>n'est pas connectée</strong> aux comptes ou aux API des opérateurs Mobile Money ;</li>
        <li><strong>n'effectue aucun paiement</strong> et ne manipule pas d'argent ;</li>
        <li><strong>ne contient aucune publicité</strong> et n'utilise pas d'outil de ciblage publicitaire.</li>
    </ul>
    <p>Si une future version introduit de nouvelles fonctionnalités (par exemple la lecture des SMS de confirmation), cette politique sera mise à jour, et l'autorisation du système (Android) ainsi que votre consentement explicite seront demandés avant toute activation.</p>

    <h2>4. Pourquoi utilisons-nous ces données ? (finalités)</h2>
    <p>Les données sont traitées uniquement pour :</p>
    <ol>
        <li>vous permettre de vous connecter et de sécuriser l'accès à votre compte ;</li>
        <li>enregistrer, consulter, rechercher, modifier et annuler vos transactions ;</li>
        <li>recueillir la confirmation du client avant chaque opération ;</li>
        <li>gérer la fiche de vos clients, pré-remplie grâce au scan de leur CNIB, et afficher leur activité (nombre d'opérations, totaux des dépôts et retraits) ;</li>
        <li>suivre votre caisse (ouverture, clôture, écarts) ;</li>
        <li>afficher votre tableau de bord et vos statistiques ;</li>
        <li>générer l'export PDF de vos transactions par période et par service ;</li>
        <li>conserver l'historique des modifications pour garantir la fiabilité de vos enregistrements ;</li>
        <li>assurer la sécurité, la maintenance et l'amélioration du service ;</li>
        <li>répondre à vos demandes d'assistance ;</li>
        <li>respecter nos obligations légales.</li>
    </ol>
    <p>Nous ne vendons pas vos données et ne les utilisons pas à des fins commerciales ou publicitaires.</p>

    <h2>5. Sur quelle base légale ?</h2>
    <p>Les traitements reposent sur : l'exécution du service que vous avez demandé (utilisation de l'Application), votre consentement lorsqu'il est requis, les obligations légales applicables et l'intérêt légitime de l'éditeur à assurer la sécurité et le bon fonctionnement du service.</p>

    <h2>6. Avec qui les données sont-elles partagées ?</h2>
    <ul>
        <li><strong>Aucun partage commercial.</strong> Vos données et celles de vos clients ne sont ni vendues ni louées.</li>
        <li><strong>Isolation entre agents.</strong> Chaque agent n'a accès qu'à ses propres clients et à ses propres transactions. Un même numéro de téléphone peut correspondre à des fiches distinctes chez des agents différents, sans lien entre elles.</li>
        <li><strong>Hébergement.</strong> Les données sont hébergées chez Hetzner (Hetzner Online GmbH, Allemagne), qui agit uniquement comme prestataire technique, dans le cadre de sa mission et sous obligation de confidentialité.</li>
        <li><strong>Envoi d'e-mails.</strong> Le code à usage unique de réinitialisation du code d'accès est envoyé par e-mail via le service de messagerie Google (Gmail), depuis l'adresse mobitrace11@gmail.com.</li>
        <li><strong>Partages à votre initiative.</strong> Lorsque vous exportez un PDF de vos transactions et le partagez (WhatsApp, e-mail, etc.), par exemple avec un inspecteur ou un contrôleur, ce partage relève de votre décision et de votre responsabilité.</li>
        <li><strong>Autorités.</strong> Les données peuvent être communiquées aux autorités judiciaires ou administratives compétentes lorsque la loi l'exige.</li>
    </ul>

    <h2>7. Où sont stockées les données ?</h2>
    <p>Les données sont hébergées chez Hetzner, sur des serveurs situés en <mark>[Allemagne / Finlande – région à préciser]</mark>, donc hors du Burkina Faso. L'équipe de développement Mobitrace s'engage à s'assurer que le pays d'hébergement offre un niveau de protection suffisant ou à mettre en place des garanties appropriées, conformément à la réglementation applicable. Les images de cartes d'identité scannées ne sont pas envoyées à ces serveurs.</p>

    <h2>8. Combien de temps les données sont-elles conservées ?</h2>
    <ul>
        <li><strong>Compte et données associées :</strong> pendant toute la durée d'utilisation de l'Application.</li>
        <li><strong>Transactions et historique :</strong> les transactions ne sont jamais supprimées physiquement ; une transaction annulée reste visible dans l'historique afin de préserver la traçabilité. Elles sont conservées pendant la durée nécessaire à l'activité de l'agent et aux obligations légales, soit <mark>[durée, par exemple 5 ans]</mark> après la dernière opération.</li>
        <li><strong>Après suppression du compte :</strong> les données sont supprimées ou anonymisées dans un délai de <mark>[30 jours]</mark>, sauf celles que la loi impose de conserver.</li>
    </ul>

    <h2>9. Comment les données sont-elles protégées ?</h2>
    <p>Nous mettons en œuvre des mesures techniques et organisationnelles adaptées :</p>
    <ul>
        <li>connexion par numéro de téléphone et code d'accès à 8 chiffres, stocké sous forme hachée, avec limitation du nombre de tentatives ;</li>
        <li>chiffrement du numéro de pièce d'identité des clients dans la base de données ;</li>
        <li>scan de la CNIB réalisé sur le téléphone, sans envoi ni conservation de l'image ;</li>
        <li>échanges chiffrés entre l'application mobile et le serveur (HTTPS) ;</li>
        <li>accès aux données limité à l'utilisateur propriétaire (cloisonnement par compte) ;</li>
        <li>journalisation des actions importantes ;</li>
        <li>sauvegardes régulières et accès restreint aux serveurs.</li>
    </ul>
    <p>Aucun système n'étant totalement infaillible, nous vous invitons à protéger votre téléphone (code de verrouillage), à ne jamais partager votre code d'accès et à vous déconnecter si vous prêtez votre appareil. En cas de violation de données susceptible de vous affecter, nous vous en informerons et préviendrons l'autorité compétente dans les conditions prévues par la loi.</p>

    <h2>10. Vos droits</h2>
    <p>Conformément à la réglementation burkinabè sur la protection des données à caractère personnel, vous disposez des droits suivants :</p>
    <ul>
        <li><strong>accès</strong> : savoir quelles données vous concernant sont traitées ;</li>
        <li><strong>rectification</strong> : corriger des données inexactes ou incomplètes ;</li>
        <li><strong>opposition</strong> : vous opposer, pour des motifs légitimes, à certains traitements ;</li>
        <li><strong>suppression</strong> : demander l'effacement de vos données, dans les limites des obligations légales de conservation ;</li>
        <li><strong>limitation et portabilité</strong> : lorsque la réglementation le permet.</li>
    </ul>
    <p>Pour exercer ces droits, écrivez à <strong><a href="mailto:mobitrace11@gmail.com">mobitrace11@gmail.com</a></strong>. Nous répondrons dans un délai raisonnable, au plus tard sous <mark>[30 jours]</mark>.</p>
    <p><strong>Pour les clients des agents :</strong> si vous êtes client d'un agent et souhaitez accéder à vos informations, les corriger ou les faire supprimer, adressez-vous d'abord à votre agent, qui est responsable de ces données ; l'éditeur l'assistera techniquement dans la mise en œuvre de votre demande.</p>
    <p>Vous pouvez également introduire une réclamation auprès de l'autorité de protection des données du Burkina Faso (la Commission de l'informatique et des libertés – CIL).</p>

    <h2>11. Obligations de l'agent envers ses clients</h2>
    <p>En tant que responsable du traitement des données de ses clients, l'agent s'engage à :</p>
    <ul>
        <li>n'enregistrer que les informations nécessaires à son activité ;</li>
        <li>informer ses clients que leurs données sont consignées dans l'Application ;</li>
        <li>ne pas utiliser ces données pour un autre usage que la gestion de ses opérations ;</li>
        <li>ne pas les communiquer à des tiers sans base légale ;</li>
        <li>respecter les demandes d'accès, de rectification ou de suppression de ses clients.</li>
    </ul>

    <h2>12. Données des mineurs</h2>
    <p>L'Application est réservée aux personnes majeures exerçant une activité d'agent Mobile Money. Elle n'est pas destinée aux mineurs.</p>

    {{-- ===================== PARTIE 2 ===================== --}}
    <h1 id="conditions" class="partie">Conditions générales d'utilisation</h1>

    <h2>13. Objet</h2>
    <p>Les présentes conditions définissent les règles d'accès et d'utilisation de l'Application, mise à disposition par <strong>l'équipe de développement Mobitrace</strong> (l'« éditeur »).</p>

    <h2>14. Description du service</h2>
    <p>L'Application est un outil de <strong>tenue de registre</strong> destiné à l'agent Mobile Money. Elle permet d'enregistrer des dépôts et des retraits avec la confirmation du client, de gérer des fiches clients (avec scan de la CNIB), de consulter et rechercher l'historique, de corriger ou annuler des transactions, de suivre sa caisse, d'exporter ses transactions en PDF et de conserver la trace de ces modifications.</p>
    <p><strong>Précisions importantes :</strong></p>
    <ul>
        <li>L'Application <strong>n'est pas un service financier</strong> : elle ne réalise aucune transaction réelle, ne détient aucun fonds et ne se substitue pas aux plateformes des opérateurs (Orange Money, Moov Money, Telecel Money, etc.).</li>
        <li>L'Application <strong>n'est affiliée à aucun opérateur</strong> Mobile Money. Les noms et logos des services sont utilisés à titre purement indicatif, pour identifier le service concerné par une opération.</li>
        <li>Le suivi de caisse est un <strong>outil de contrôle indicatif</strong>, calculé à partir des opérations que vous saisissez ; il ne remplace ni les soldes réels de vos comptes ni une comptabilité. L'Application ne calcule pas de commissions.</li>
    </ul>

    <h2>15. Accès et compte utilisateur</h2>
    <ul>
        <li>L'utilisation de l'Application nécessite la création d'un compte et une <strong>connexion Internet</strong>.</li>
        <li>L'utilisateur doit être majeur et fournir des informations exactes.</li>
        <li>Les identifiants sont <strong>personnels et confidentiels</strong>. L'utilisateur est responsable de toute activité réalisée avec son compte et doit signaler sans délai toute utilisation non autorisée à <strong><a href="mailto:mobitrace11@gmail.com">mobitrace11@gmail.com</a></strong>.</li>
    </ul>

    <h2>16. Obligations de l'utilisateur</h2>
    <p>L'utilisateur s'engage à :</p>
    <ul>
        <li>utiliser l'Application de manière licite et conforme à la réglementation applicable à son activité d'agent, notamment les règles de son opérateur ;</li>
        <li>saisir des informations exactes et vérifier les données avant enregistrement ;</li>
        <li>ne pas tenter d'accéder aux données d'autres utilisateurs, de contourner la sécurité ou de perturber le service ;</li>
        <li>ne pas utiliser l'Application à des fins frauduleuses, de blanchiment d'argent ou de financement d'activités illicites ;</li>
        <li>ne pas copier, décompiler ou revendre l'Application.</li>
    </ul>

    <h2>17. Traçabilité et intégrité des données</h2>
    <p>Les transactions ne peuvent pas être supprimées définitivement : elles peuvent uniquement être <strong>modifiées</strong> ou <strong>annulées</strong>, et chaque action est consignée dans un historique (date, auteur, anciennes et nouvelles valeurs, motif). L'utilisateur accepte ce principe de conservation, qui garantit la fiabilité de ses enregistrements.</p>

    <h2>18. Propriété intellectuelle</h2>
    <p>L'Application, son code, son interface, son nom et ses contenus sont la propriété de <strong>l'équipe de développement Mobitrace</strong> et protégés par la législation sur la propriété intellectuelle. Une licence d'utilisation personnelle, non exclusive, non cessible et révocable est accordée à l'utilisateur. Les données saisies par l'utilisateur restent sa propriété.</p>

    <h2>19. Disponibilité et évolution du service</h2>
    <p>L'éditeur s'efforce d'assurer un accès continu à l'Application, mais ne garantit pas une disponibilité sans interruption (maintenance, panne réseau, force majeure). Le service peut évoluer ; les nouvelles fonctionnalités et les modifications importantes seront annoncées dans l'Application ou par e-mail.</p>

    <h2>20. Limitation de responsabilité</h2>
    <ul>
        <li>L'Application est un outil d'aide à l'enregistrement. L'utilisateur reste <strong>seul responsable</strong> de l'exactitude des données saisies et de ses opérations réelles auprès des opérateurs.</li>
        <li>L'éditeur ne peut être tenu responsable des erreurs de saisie, des litiges entre l'agent et ses clients ou l'opérateur, des pertes liées à un usage non conforme, ni des interruptions dues à des facteurs extérieurs (Internet, électricité, téléphone de l'utilisateur).</li>
        <li>Il est recommandé à l'utilisateur de rapprocher régulièrement ses enregistrements des relevés de son opérateur.</li>
    </ul>
    <p>Ces limitations s'appliquent dans la mesure permise par la loi.</p>

    <h2>21. Suspension et résiliation</h2>
    <p>L'utilisateur peut cesser d'utiliser l'Application et demander la suppression de son compte à tout moment. L'éditeur peut suspendre ou résilier un compte en cas de violation des présentes conditions ou d'usage frauduleux, après information de l'utilisateur lorsque cela est possible.</p>

    <h2>22. Modification des documents</h2>
    <p>L'éditeur peut modifier la présente politique et les présentes conditions, notamment lors de l'ajout de nouvelles fonctionnalités. Les utilisateurs seront informés des changements importants ; la poursuite de l'utilisation de l'Application après notification vaut acceptation, sauf si un nouveau consentement est légalement requis.</p>

    <h2>23. Droit applicable et litiges</h2>
    <p>Les présentes sont régies par le droit burkinabè. En cas de différend, les parties chercheront d'abord une solution amiable ; à défaut, les tribunaux compétents de Ouagadougou seront saisis.</p>

    <h2>24. Contact</h2>
    <p><strong>Équipe de développement Mobitrace</strong><br>
        Adresse : Ouagadougou, Burkina Faso<br>
        E-mail : <a href="mailto:mobitrace11@gmail.com">mobitrace11@gmail.com</a></p>

    <footer>
        <a href="#confidentialite">Politique de confidentialité</a> · <a href="#conditions">Conditions d'utilisation</a>
    </footer>

</main>
</body>
</html>