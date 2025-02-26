#**************** BASE DE DONNEES sgpc ****************
# Le : 02 11 2020 a 16h 05
# Serveur : localhost:8000
# Version PHP : 7.4.0
# Version mySQL : 10.5.4-MariaDB
# IP Client : ::1
# Fichier SQL compatible PHPMyadmin
#
# ******* debut du fichier ********
#
# Nettoyage de la base
#
#
# Construction de la base 
#
#
# Structure de la table cautionaryAdvice
#
CREATE TABLE `cautionaryadvice` (
  `id_cautionaryAdvice` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_cautionaryAdvice` varchar(50) NOT NULL,
  `sentenceCautionaryAdvice` varchar(500) NOT NULL,
  PRIMARY KEY (`id_cautionaryAdvice`),
  UNIQUE KEY `id_cautionaryAdvice` (`id_cautionaryAdvice`),
  UNIQUE KEY `name_cautionaryAdvice` (`name_cautionaryAdvice`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8;
#
# Données de cautionaryAdvice
#
INSERT INTO cautionaryAdvice  values ('1', 'H200', 'Explosif instable.');
INSERT INTO cautionaryAdvice  values ('2', 'H201', 'Explosif danger d\'explosion en masse.');
INSERT INTO cautionaryAdvice  values ('3', 'H202', 'Explosif danger sérieux de projection');
INSERT INTO cautionaryAdvice  values ('4', 'H203', 'Explosif danger d\'incendie, d\'effet de souffle ou de projection.');
INSERT INTO cautionaryAdvice  values ('5', 'H204', 'Danger d\'incendie ou de projection.');
INSERT INTO cautionaryAdvice  values ('6', 'H205', 'Danger d\'explosion en masse en cas d\'incendie.');
INSERT INTO cautionaryAdvice  values ('7', 'H220', 'Gaz extrêmement inflammable');
INSERT INTO cautionaryAdvice  values ('8', 'H221', 'Gaz inflammable.');
INSERT INTO cautionaryAdvice  values ('9', 'H222', 'Aérosol extrêmement inflammable');
INSERT INTO cautionaryAdvice  values ('10', 'H223', 'Aérosol inflammable');
INSERT INTO cautionaryAdvice  values ('11', 'H224', 'Liquide et vapeurs extrêmement inflammables');
INSERT INTO cautionaryAdvice  values ('12', 'H225', 'Liquide et vapeurs très inflammables');
INSERT INTO cautionaryAdvice  values ('13', 'H226', 'Liquide et vapeurs inflammables.');
INSERT INTO cautionaryAdvice  values ('14', 'H228', 'Matière solide inflammable');
INSERT INTO cautionaryAdvice  values ('15', 'H240', 'Peut exploser sous l\'effet de la chaleur.');
INSERT INTO cautionaryAdvice  values ('16', 'H241', 'Peut s\'enflammer ou exploser sous l\'effet de la chaleur.');
INSERT INTO cautionaryAdvice  values ('17', 'H242', 'Peut s\'enflammer sous l\'effet de la chaleur.');
INSERT INTO cautionaryAdvice  values ('18', 'H250', 'S\'enflamme spontanément au contact de l\'air');
INSERT INTO cautionaryAdvice  values ('19', 'H251', 'Matière auto-échauffante. peut s\'enflamme');
INSERT INTO cautionaryAdvice  values ('20', 'H252', 'Matière auto-échauffante en grandes quantités. peut s\'enflammer');
INSERT INTO cautionaryAdvice  values ('21', 'H260', 'Dégage au contact de l\'eau des gaz inflammables qui peuvent s\'enflammer spontanément');
INSERT INTO cautionaryAdvice  values ('22', 'H261', 'Dégage au contact de l\'eau des gaz inflammables');
INSERT INTO cautionaryAdvice  values ('23', 'H270', 'Peut provoquer ou aggraver un incendie. comburant.');
INSERT INTO cautionaryAdvice  values ('24', 'H271', 'Peut provoquer un incendie ou une explosion. comburant puissant.');
INSERT INTO cautionaryAdvice  values ('25', 'H272', 'Peut aggraver un incendie. comburant.');
INSERT INTO cautionaryAdvice  values ('26', 'H280', 'Contient un gaz sous pression. peut exploser sous l\'effet de la chaleur.');
INSERT INTO cautionaryAdvice  values ('27', 'H281', 'Contient un gaz réfrigéré. peut causer des brûlures ou blessures cryogéniques');
INSERT INTO cautionaryAdvice  values ('28', 'H290', 'Peut être corrosif pour les métaux');
INSERT INTO cautionaryAdvice  values ('29', 'H300', 'Mortel en cas d’ingestion');
INSERT INTO cautionaryAdvice  values ('30', 'H301', 'Toxique en cas d’ingestion');
INSERT INTO cautionaryAdvice  values ('31', 'H302', 'Nocif en cas d’ingestion');
INSERT INTO cautionaryAdvice  values ('32', 'H304', 'Peut être mortel en cas d’ingestion et de pénétration dans les voies respiratoires');
INSERT INTO cautionaryAdvice  values ('33', 'H310', 'Mortel par contact cutané');
INSERT INTO cautionaryAdvice  values ('34', 'H311', 'Toxique par contact cutané');
INSERT INTO cautionaryAdvice  values ('35', 'H312', 'Nocif par contact cutané');
INSERT INTO cautionaryAdvice  values ('36', 'H314', 'Provoque des brûlures de la peau et de graves lésions des yeux');
INSERT INTO cautionaryAdvice  values ('37', 'H315', 'Provoque une irritation cutanée');
INSERT INTO cautionaryAdvice  values ('38', 'H317', 'Peut provoquer une allergie cutanée');
INSERT INTO cautionaryAdvice  values ('39', 'H318', 'Provoque de graves lésions des yeux');
INSERT INTO cautionaryAdvice  values ('40', 'H319', 'Provoque une grave irritation oculaire.');
INSERT INTO cautionaryAdvice  values ('41', 'H330', 'Mortel par inhalation.');
INSERT INTO cautionaryAdvice  values ('42', 'H331', 'Toxique par inhalation.');
INSERT INTO cautionaryAdvice  values ('43', 'H332', 'Nocif par inhalation.');
INSERT INTO cautionaryAdvice  values ('44', 'H334', 'Peut provoquer des symptômes allergiques ou d’asthme ou des difficultés respiratoires par inhalation');
INSERT INTO cautionaryAdvice  values ('45', 'H335', 'Peut irriter les voies respiratoires.');
INSERT INTO cautionaryAdvice  values ('46', 'H336', 'Peut provoquer somnolence ou vertiges.');
#
# Structure de la table dangerNote
#
CREATE TABLE `dangernote` (
  `id_dangerNote` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_dangerNote` varchar(50) NOT NULL,
  `sentenceDangerNote` varchar(500) NOT NULL,
  PRIMARY KEY (`id_dangerNote`),
  UNIQUE KEY `id_dangerNote` (`id_dangerNote`),
  UNIQUE KEY `name_dangerNote` (`name_dangerNote`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;
#
# Données de dangerNote
#
INSERT INTO dangerNote  values ('1', 'P101', 'En cas de consultation d’un médecin, garder à disposition le récipient ou l’étiquette.');
INSERT INTO dangerNote  values ('2', 'P102', 'Tenir hors de portée des enfants.');
INSERT INTO dangerNote  values ('3', 'P103', 'Lire l’étiquette avant utilisation.');
INSERT INTO dangerNote  values ('4', 'P201', 'Se procurer les instructions spéciales avant utilisation.');
INSERT INTO dangerNote  values ('5', 'P202', 'Ne pas manipuler avant d’avoir lu et compris toutes les dispositions de sécurité.');
INSERT INTO dangerNote  values ('6', 'P210', 'Tenir à l’écart de la chaleur, des surfaces chaudes, des étincelles, des flammes nues et de toute autre source d’inflammation. Ne pas fumer.');
INSERT INTO dangerNote  values ('7', 'P211', 'Ne pas vaporiser sur une flamme nue ou sur toute autre source d’ignition.');
INSERT INTO dangerNote  values ('8', 'P220', 'Tenir à l\'écart des vêtements et d\'autres matières combustibles.');
INSERT INTO dangerNote  values ('9', 'P222', 'Ne pas laisser au contact de l’air.');
INSERT INTO dangerNote  values ('10', 'P223', 'Éviter tout contact avec l’eau.');
INSERT INTO dangerNote  values ('11', 'P230', 'Maintenir humidifié avec...');
INSERT INTO dangerNote  values ('12', 'P231', 'Manipuler et stocker le contenu sous gaz inerte/…');
INSERT INTO dangerNote  values ('13', 'P232', 'Protéger de l’humidité.');
INSERT INTO dangerNote  values ('14', 'P233', 'Maintenir le récipient fermé de manière étanche.');
INSERT INTO dangerNote  values ('15', 'P234', 'Conserver uniquement dans l\'emballage d\'origine.');
INSERT INTO dangerNote  values ('16', 'P235', 'Tenir au frais.');
INSERT INTO dangerNote  values ('17', 'P240', 'Mise à la terre et liaison équipotentielle du récipient et du matériel de réception.');
INSERT INTO dangerNote  values ('18', 'P241', 'Utiliser du matériel [électrique/de ventilation/d\'éclairage/…] antidéflagrant.');
INSERT INTO dangerNote  values ('19', 'P242', 'Utiliser des outils ne produisant pas d\'étincelles.');
INSERT INTO dangerNote  values ('20', 'P243', 'Prendre des mesures de précaution contre les décharges électrostatiques.');
INSERT INTO dangerNote  values ('21', 'P244', 'Ni huile, ni graisse sur les robinets et raccords.');
INSERT INTO dangerNote  values ('22', 'P250', 'Éviter les abrasions/les chocs/les frottements/… .');
INSERT INTO dangerNote  values ('23', 'P251', 'Ne pas perforer, ni brûler, même après usage.');
INSERT INTO dangerNote  values ('24', 'P260', 'Ne pas respirer les poussières/fumées/gaz/brouillards/vapeurs/aérosols.');
INSERT INTO dangerNote  values ('25', 'P261', 'Éviter de respirer les poussières/fumées/gaz/brouillards/vapeurs/aérosols.');
INSERT INTO dangerNote  values ('26', 'P262', 'Éviter tout contact avec les yeux, la peau ou les vêtements.');
INSERT INTO dangerNote  values ('27', 'P263', 'Éviter tout contact avec la substance au cours de la grossesse et pendant l\'allaitement.');
INSERT INTO dangerNote  values ('28', 'P264', 'Laver … soigneusement après manipulation.');
INSERT INTO dangerNote  values ('29', 'P270', 'Ne pas manger, boire ou fumer en manipulant ce produit.');
INSERT INTO dangerNote  values ('30', 'P271', 'Utiliser seulement en plein air ou dans un endroit bien ventilé.');
INSERT INTO dangerNote  values ('31', 'P272', 'Les vêtements de travail contaminés ne devraient pas sortir du lieu de travail.');
INSERT INTO dangerNote  values ('32', 'P273', 'Éviter le rejet dans l’environnement.');
INSERT INTO dangerNote  values ('33', 'P280', 'Porter des gants de protection/des vêtements de protection/un équipement de protection des yeux/du visage.');
INSERT INTO dangerNote  values ('34', 'P282', 'Porter des gants isolants contre le froid et un équipement de protection du visage ou des yeux.');
INSERT INTO dangerNote  values ('35', 'P283', 'Porter des vêtements résistant au feu ou à retard de flamme.');
INSERT INTO dangerNote  values ('36', 'P284', '[Lorsque la ventilation du local est insuffisante] porter un équipement de protection respiratoire.');
INSERT INTO dangerNote  values ('37', 'P231 + P232', 'Manipuler et stocker le contenu sous gaz inerte/… Protéger de l\'humidité.');
INSERT INTO dangerNote  values ('38', 'P301', 'EN CAS D’INGESTION:');
INSERT INTO dangerNote  values ('39', 'P302', 'EN CAS DE CONTACT AVEC LA PEAU:');
INSERT INTO dangerNote  values ('40', 'P303', 'EN CAS DE CONTACT AVEC LA PEAU (ou les cheveux):');
INSERT INTO dangerNote  values ('41', 'P304', 'EN CAS D’INHALATION:');
INSERT INTO dangerNote  values ('42', 'P305', 'EN CAS DE CONTACT AVEC LES YEUX:');
INSERT INTO dangerNote  values ('43', 'P306', 'EN CAS DE CONTACT AVEC LES VÊTEMENTS:');
INSERT INTO dangerNote  values ('44', 'P308', 'EN CAS d’exposition prouvée ou suspectée:');
INSERT INTO dangerNote  values ('45', 'P310', 'Appeler immédiatement un CENTRE ANTIPOISON/un médecin/…');
INSERT INTO dangerNote  values ('46', 'P311', 'Appeler un CENTRE ANTIPOISON/un médecin/…');
INSERT INTO dangerNote  values ('47', 'P312', 'Appeler un CENTRE ANTIPOISON/un médecin/…/ en cas de malaise.');
INSERT INTO dangerNote  values ('48', 'P313', 'Consulter un médecin.');
INSERT INTO dangerNote  values ('49', 'P314', 'Consulter un médecin en cas de malaise.');
INSERT INTO dangerNote  values ('50', 'P315', 'Consulter immédiatement un médecin.');
INSERT INTO dangerNote  values ('51', 'P320', 'Un traitement spécifique est urgent (voir ... sur cette étiquette).');
INSERT INTO dangerNote  values ('52', 'P321', 'Traitement spécifique (voir ... sur cette étiquette).');
INSERT INTO dangerNote  values ('53', 'P330', 'Rincer la bouche.');
INSERT INTO dangerNote  values ('54', 'P331', 'NE PAS faire vomir.');
INSERT INTO dangerNote  values ('55', 'P332', 'En cas d’irritation cutanée:');
INSERT INTO dangerNote  values ('56', 'P333', 'En cas d’irritation ou d’éruption cutanée:');
INSERT INTO dangerNote  values ('57', 'P334', 'Rincer à l\'eau fraîche [ou poser une compresse humide].');
INSERT INTO dangerNote  values ('58', 'P335', 'Enlever avec précaution les particules déposées sur la peau.');
INSERT INTO dangerNote  values ('59', 'P336', 'Dégeler les parties gelées avec de l’eau tiède. Ne pas frotter les zones touchées.');
INSERT INTO dangerNote  values ('60', 'P337', 'Si l’irritation oculaire persiste:');
INSERT INTO dangerNote  values ('61', 'P338', 'Enlever les lentilles de contact si la victime en porte et si elles peuvent être facilement enlevées. Continuer à rincer.');
INSERT INTO dangerNote  values ('62', 'P340', 'Transporter la personne à l’extérieur et la maintenir dans une position où elle peut confortablement respirer.');
INSERT INTO dangerNote  values ('63', 'P342', 'En cas de symptômes respiratoires:');
INSERT INTO dangerNote  values ('64', 'P351', 'Rincer avec précaution à l’eau pendant plusieurs minutes.');
INSERT INTO dangerNote  values ('65', 'P352', 'Laver abondamment à l’eau/…');
INSERT INTO dangerNote  values ('66', 'P353', 'Rincer la peau à l\'eau [ou se doucher].');
INSERT INTO dangerNote  values ('67', 'P360', 'Rincer immédiatement et abondamment avec de l’eau les vêtements contaminés et la peau avant de les enlever.');
INSERT INTO dangerNote  values ('68', 'P361', 'Enlever immédiatement tous les vêtements contaminés.');
INSERT INTO dangerNote  values ('69', 'P362', 'Enlever les vêtements contaminés.');
INSERT INTO dangerNote  values ('70', 'P363', 'Laver les vêtements contaminés avant réutilisation.');
INSERT INTO dangerNote  values ('71', 'P370', 'En cas d’incendie:');
INSERT INTO dangerNote  values ('72', 'P371', 'En cas d’incendie important et s’il s’agit de grandes quantités:');
INSERT INTO dangerNote  values ('73', 'P372', 'Risque d\'explosion.');
INSERT INTO dangerNote  values ('74', 'P373', 'NE PAS combattre l’incendie lorsque le feu atteint les explosifs.');
INSERT INTO dangerNote  values ('75', 'P375', 'Combattre l’incendie à distance à cause du risque d’explosion.');
INSERT INTO dangerNote  values ('76', 'P376', 'Obturer la fuite si cela peut se faire sans danger.');
INSERT INTO dangerNote  values ('77', 'P377', 'Fuite de gaz enflammé: Ne pas éteindre si la fuite ne peut pas être arrêtée sans danger.');
INSERT INTO dangerNote  values ('78', 'P378', 'Utiliser… pour l’extinction.');
INSERT INTO dangerNote  values ('79', 'P380', 'Évacuer la zone.');
INSERT INTO dangerNote  values ('80', 'P381', 'En cas de fuite, éliminer toutes les sources d\'ignition.');
INSERT INTO dangerNote  values ('81', 'P390', 'Absorber toute substance répandue pour éviter qu’elle attaque les matériaux environnants.');
INSERT INTO dangerNote  values ('82', 'P391', 'Recueillir le produit répandu.');
INSERT INTO dangerNote  values ('83', 'P301 + P310', 'EN CAS D’INGESTION: Appeler immédiatement un CENTRE ANTIPOISON/un médecin/…');
INSERT INTO dangerNote  values ('84', 'P301 + P312', 'EN CAS D\'INGESTION: Appeler un CENTRE ANTIPOISON/un médecin/…/ en cas de malaise.');
INSERT INTO dangerNote  values ('85', 'P301 + P330 + P331', 'EN CAS D’INGESTION: rincer la bouche. NE PAS faire vomir.');
INSERT INTO dangerNote  values ('86', 'P302 + P334', 'EN CAS DE CONTACT AVEC LA PEAU: Rincer à l\'eau fraîche ou poser une compresse humide.');
INSERT INTO dangerNote  values ('87', 'P302 + P352', 'EN CASO DE CONTACTO CON LA PIEL: Laver abondamment à l’eau/…');
INSERT INTO dangerNote  values ('88', 'P303 + P361 + P353', 'EN CAS DE CONTACT AVEC LA PEAU (ou les cheveux): Enlever immédiatement tous les vêtements contaminés. Rincer la peau à l\'eau [ou se doucher].');
INSERT INTO dangerNote  values ('89', 'P304 + P340', 'EN CAS D’INHALATION: transporter la personne à l’extérieur et la maintenir dans une position où elle peut confortablement respirer.');
INSERT INTO dangerNote  values ('90', 'P305 + P351 + P338', 'EN CAS DE CONTACT AVEC LES YEUX: rincer avec précaution à l’eau pendant plusieurs minutes. Enlever les lentilles de contact si la victime en porte et si elles peuvent être facilement enlevées. Continuer à rincer.');
INSERT INTO dangerNote  values ('91', 'P306 + P360', 'EN CAS DE CONTACT AVEC LES VÊTEMENTS: rincer immédiatement et abondamment avec de l’eau les vêtements contaminés et la peau avant de les enlever.');
INSERT INTO dangerNote  values ('92', 'P308 + P313', 'EN CAS d’exposition prouvée ou suspectée: consulter un médecin.');
INSERT INTO dangerNote  values ('93', 'P332 + P313', 'En cas d’irritation cutanée: consulter un médecin.');
INSERT INTO dangerNote  values ('94', 'P333 + P313', 'En cas d’irritation ou d\'éruption cutanée: consulter un médecin.');
INSERT INTO dangerNote  values ('95', 'P337 + P313', 'Si l’irritation oculaire persiste: consulter un médecin.');
INSERT INTO dangerNote  values ('96', 'P342 + P311', 'En cas de symptômes respiratoires: Appeler un CENTRE ANTIPOISON/un médecin/…');
INSERT INTO dangerNote  values ('97', 'P370 + P376', 'En cas d’incendie: obturer la fuite si cela peut se faire sans danger.');
INSERT INTO dangerNote  values ('98', 'P370 + P378', 'En cas d’incendie: Utiliser… pour l’extinction.');
INSERT INTO dangerNote  values ('99', 'P370 + P380 + P375', 'En cas d’incendie: évacuer la zone. Combattre l’incendie à distance à cause du risque d’explosion.');
INSERT INTO dangerNote  values ('100', 'P371 + P380 + P375', 'En cas d’incendie important et s’il s’agit de grandes quantités: évacuer la zone. Combattre l’incendie à distance à cause du risque d’explosion.');
INSERT INTO dangerNote  values ('101', 'P401', 'Stocker conformément à… .');
INSERT INTO dangerNote  values ('102', 'P402', 'Stocker dans un endroit sec.');
INSERT INTO dangerNote  values ('103', 'P403', 'Stocker dans un endroit bien ventilé.');
INSERT INTO dangerNote  values ('104', 'P404', 'Stocker dans un récipient fermé.');
INSERT INTO dangerNote  values ('105', 'P405', 'Garder sous clef.');
INSERT INTO dangerNote  values ('106', 'P406', 'Stocker dans un récipient résistant à la corrosion/… avec doublure intérieure.');
INSERT INTO dangerNote  values ('107', 'P407', 'Maintenir un intervalle d\'air entre les piles ou les palettes.');
INSERT INTO dangerNote  values ('108', 'P410', 'Protéger du rayonnement solaire.');
INSERT INTO dangerNote  values ('109', 'P411', 'Stocker à une température ne dépassant pas ... °C/... °F.');
INSERT INTO dangerNote  values ('110', 'P412', 'Ne pas exposer à une température supérieure à 50 °C/122 °F.');
INSERT INTO dangerNote  values ('111', 'P413', 'Stocker les quantités en vrac de plus de ... kg/... lb à une température ne dépassant pas ... °C/... °F.');
INSERT INTO dangerNote  values ('112', 'P420', 'Stocker séparément.');
INSERT INTO dangerNote  values ('113', 'P402 + P404', 'Stocker dans un endroit sec. Stocker dans un récipient fermé.');
INSERT INTO dangerNote  values ('114', 'P403 + P233', 'Stocker dans un endroit bien ventilé. Maintenir le récipient fermé de manière étanche.');
INSERT INTO dangerNote  values ('115', 'P403 + P235', 'Stocker dans un endroit bien ventilé. Tenir au frais.');
INSERT INTO dangerNote  values ('116', 'P410 + P403', 'Protéger du rayonnement solaire. Stocker dans un endroit bien ventilé.');
INSERT INTO dangerNote  values ('117', 'P410 + P412', 'Protéger du rayonnement solaire. Ne pas exposer à une température supérieure à 50 °C/ 122 °F.');
INSERT INTO dangerNote  values ('118', 'P501', 'Éliminer le contenu/récipient dans ...');
#
# Structure de la table dangerSymbol
#
CREATE TABLE `dangersymbol` (
  `id_dangerSymbol` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_dangerSymbol` char(5) NOT NULL,
  `description_dangerSymbol` varchar(250) NOT NULL,
  `icon` varchar(100) NOT NULL,
  PRIMARY KEY (`id_dangerSymbol`),
  UNIQUE KEY `id_dangerSymbol` (`id_dangerSymbol`),
  UNIQUE KEY `name_dangerSymbol` (`name_dangerSymbol`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
#
# Données de dangerSymbol
#
INSERT INTO dangerSymbol  values ('1', 'SGH01', 'Explosif', 'SGH01_BombeExplosant.jpg');
INSERT INTO dangerSymbol  values ('2', 'SGH02', 'Inflammable', 'SGH02_Flamme.jpg');
INSERT INTO dangerSymbol  values ('3', 'SGH03', 'Comburant', 'SGH03_FlammeSurCercle.jpg');
INSERT INTO dangerSymbol  values ('4', 'SGH04', 'Gaz sous pression', 'SGH04_BouteilleGaz.jpg');
INSERT INTO dangerSymbol  values ('5', 'SGH05', 'Corrosif', 'SGH05_Corrosion.jpg');
INSERT INTO dangerSymbol  values ('6', 'SGH06', 'Toxique', 'SGH06_TeteDeMort.jpg');
INSERT INTO dangerSymbol  values ('7', 'SGH07', 'Toxique, irritant, sensibilisant, narcotique', 'SGH07_PointExclamation.jpg');
INSERT INTO dangerSymbol  values ('8', 'SGH08', 'Sensibilisant, cancérogène, mutagène, reprotoxique', 'SGH08_DangerSante.jpg');
INSERT INTO dangerSymbol  values ('9', 'SGH09', 'Danger pour l\'environnement', 'SGH09_Environnement.jpg');
#
# Structure de la table analysisfile
#
CREATE TABLE `analysisfile` (
  `id_analysisfile` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_analysisfile` varchar(250) NOT NULL,
  PRIMARY KEY (`id_analysisfile`),
  UNIQUE KEY `id_analysisfile` (`id_analysisfile`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
#
# Données de analysisfile
#
INSERT INTO analysisfile  values ('1', 'fds-acetate-cuivre-fm-5f8d96079d55e.pdf');
#
# Structure de la table securityfile
#
CREATE TABLE `securityfile` (
  `id_securityfile` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_securityfile` varchar(250) NOT NULL,
  PRIMARY KEY (`id_securityfile`),
  UNIQUE KEY `id_securityfile` (`id_securityfile`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
#
# Données de securityfile
#
INSERT INTO securityfile  values ('1', 'fds-acetate-cuivre-fm-5f8d96075c257.pdf');
#
# Structure de la table privilege
#
CREATE TABLE `privilege` (
  `id_privilege` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `keyPrivilege` varchar(50) NOT NULL,
  PRIMARY KEY (`id_privilege`),
  UNIQUE KEY `id_privilege` (`id_privilege`),
  UNIQUE KEY `keyPrivilege` (`keyPrivilege`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de privilege
#
#
# Structure de la table property
#
CREATE TABLE `property` (
  `id_property` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_property` varchar(250) NOT NULL,
  PRIMARY KEY (`id_property`),
  UNIQUE KEY `id_property` (`id_property`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
#
# Données de property
#
INSERT INTO property  values ('1', 'Verena');
INSERT INTO property  values ('2', 'Delphine');
INSERT INTO property  values ('3', 'Pierrick');
INSERT INTO property  values ('4', 'Éric');
INSERT INTO property  values ('5', 'Tristan');
#
# Structure de la table site
#
CREATE TABLE `site` (
  `id_site` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_site` varchar(50) NOT NULL,
  `fullnameSupervisor` varchar(250) NOT NULL,
  `phoneNumber` char(10) NOT NULL,
  PRIMARY KEY (`id_site`),
  UNIQUE KEY `id_site` (`id_site`),
  UNIQUE KEY `name_site` (`name_site`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
#
# Données de site
#
INSERT INTO site  values ('1', 'Bâtiment A', 'Pierric Hubert', '0102030405');
INSERT INTO site  values ('2', 'Bâtiment E', 'Eric Lefevre', '0102030506');
INSERT INTO site  values ('3', 'Chimie-Organique', 'Chantal Peiffert', '0102030607');
INSERT INTO site  values ('4', 'Informatique', 'Delphine Martin', '0372745565');
#
# Structure de la table status
#
CREATE TABLE `status` (
  `id_status` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_status` varchar(50) NOT NULL,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `id_status` (`id_status`),
  UNIQUE KEY `name_status` (`name_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
#
# Données de status
#
INSERT INTO status  values ('4', 'administrateur');
INSERT INTO status  values ('3', 'responsable');
INSERT INTO status  values ('2', 'utilisateur');
INSERT INTO status  values ('1', 'visiteur');
#
# Structure de la table supplier
#
CREATE TABLE `supplier` (
  `id_supplier` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_supplier` varchar(250) NOT NULL,
  PRIMARY KEY (`id_supplier`),
  UNIQUE KEY `id_supplier` (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
#
# Données de supplier
#
INSERT INTO supplier  values ('1', 'Merck');
INSERT INTO supplier  values ('2', 'Normapur');
INSERT INTO supplier  values ('3', 'JMC');
INSERT INTO supplier  values ('4', 'Magasin');
INSERT INTO supplier  values ('5', 'Prolabo');
#
# Structure de la table type
#
CREATE TABLE `type` (
  `id_type` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_type` varchar(50) NOT NULL,
  PRIMARY KEY (`id_type`),
  UNIQUE KEY `id_type` (`id_type`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
#
# Données de type
#
INSERT INTO type  values ('1', 'Explosif');
INSERT INTO type  values ('2', 'Inflammable');
INSERT INTO type  values ('3', 'Comburant');
INSERT INTO type  values ('4', 'Gaz sous pression');
INSERT INTO type  values ('5', 'Corrosif');
INSERT INTO type  values ('6', 'Toxique');
INSERT INTO type  values ('7', 'Toxique, irritant, sensibilisant, narcotique');
INSERT INTO type  values ('8', 'Sensibilisant, cancérogène, mutagène, reprotoxique');
INSERT INTO type  values ('9', 'Danger pour l\'environnement');
INSERT INTO type  values ('10', 'Acide');
INSERT INTO type  values ('11', 'Base');
INSERT INTO type  values ('12', 'Gazeux');
INSERT INTO type  values ('13', 'Liquide');
INSERT INTO type  values ('14', 'Solide');
INSERT INTO type  values ('15', 'Odorant');
#
# Structure de la table ACL
#
CREATE TABLE `acl` (
  `id_status` bigint(20) unsigned NOT NULL,
  `id_privilege` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_status`,`id_privilege`),
  KEY `FK_ACL_privilege` (`id_privilege`),
  CONSTRAINT `FK_ACL_privilege` FOREIGN KEY (`id_privilege`) REFERENCES `privilege` (`id_privilege`),
  CONSTRAINT `FK_ACL_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de ACL
#
#
# Structure de la table chimicalProduct
#
CREATE TABLE `chimicalproduct` (
  `id_chimicalProduct` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_chimicalProduct` varchar(250) NOT NULL,
  `formula` varchar(250) DEFAULT NULL,
  `CASNumber` varchar(25) DEFAULT NULL,
  `isCMR` tinyint(1) NOT NULL,
  `solvent` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id_chimicalProduct`),
  UNIQUE KEY `id_chimicalProduct` (`id_chimicalProduct`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;
#
# Données de chimicalProduct
#
INSERT INTO chimicalProduct  values ('1', 'Di-iodométhane', 'CH2I2', '75-11-6', '0', '');
INSERT INTO chimicalProduct  values ('2', 'Sodium cyanure', 'NaCN', '143-33-9', '1', '');
INSERT INTO chimicalProduct  values ('3', 'Mercure (II) thiocyanate', 'Hg(SCN)2', '592-85-8', '1', '');
INSERT INTO chimicalProduct  values ('4', 'Gold potassium', 'Kau(CN)2', '13967-50-5', '1', '');
INSERT INTO chimicalProduct  values ('5', 'Mercure (II) chlorure', 'HgCl2', '7487-94-7', '1', '');
INSERT INTO chimicalProduct  values ('6', 'Di-arsenic trioxyde', 'As2O3', '1327-53-3', '1', '');
INSERT INTO chimicalProduct  values ('7', 'Zinc cyanure', 'Zn(CN)2', '557-21-1', '1', '');
INSERT INTO chimicalProduct  values ('8', 'Bromoforme', 'CHBr3', '75-25-2', '1', '');
INSERT INTO chimicalProduct  values ('9', 'Produit épuisé', 'NuL3-', '', '0', '');
INSERT INTO chimicalProduct  values ('10', 'Produit dangereux', 'DaNg3R', '13585-54', '1', '');
INSERT INTO chimicalProduct  values ('11', 'Produit en analyse', 'AnLyS3-', '', '0', '');
INSERT INTO chimicalProduct  values ('12', 'Produit archivé', 'ArChI', '75-25-2', '1', '');
INSERT INTO chimicalProduct  values ('13', 'Produit avec solvant', 'SoL', '', '0', 'Solvant Lambda');
INSERT INTO chimicalProduct  values ('14', 'Produit périmé', 'P3RIM2', '', '0', '');
INSERT INTO chimicalProduct  values ('15', 'Tungstate sodium', 'Na2WO4, 2H2O', '1314-62-1', '1', '');
INSERT INTO chimicalProduct  values ('16', 'Produit avec Type Acide', '', '', '0', '');
INSERT INTO chimicalProduct  values ('17', 'Produit avec Type Base', '', '', '0', '');
#
# Structure de la table stock
#
CREATE TABLE `stock` (
  `id_stock` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_site` bigint(20) unsigned NOT NULL,
  `name_stock` varchar(100) NOT NULL,
  PRIMARY KEY (`id_stock`),
  UNIQUE KEY `id_stock` (`id_stock`),
  KEY `FK_stock_site` (`id_site`),
  CONSTRAINT `FK_stock_site` FOREIGN KEY (`id_site`) REFERENCES `site` (`id_site`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
#
# Données de stock
#
INSERT INTO stock  values ('1', '1', 'Étage 1');
INSERT INTO stock  values ('2', '1', 'Étage 2');
INSERT INTO stock  values ('3', '1', 'Étage 3');
INSERT INTO stock  values ('4', '1', 'Cabane au fond du jardin');
INSERT INTO stock  values ('5', '3', 'Bureau principal');
#
# Structure de la table cupboard
#
CREATE TABLE `cupboard` (
  `id_cupboard` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_stock` bigint(20) unsigned NOT NULL,
  `name_cupboard` varchar(100) NOT NULL,
  PRIMARY KEY (`id_cupboard`),
  UNIQUE KEY `id_cupboard` (`id_cupboard`),
  KEY `FK_cupboard_stock` (`id_stock`),
  CONSTRAINT `FK_cupboard_stock` FOREIGN KEY (`id_stock`) REFERENCES `stock` (`id_stock`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;
#
# Données de cupboard
#
INSERT INTO cupboard  values ('1', '1', 'Armoire A');
INSERT INTO cupboard  values ('2', '1', 'Armoire B');
INSERT INTO cupboard  values ('3', '1', 'Armoire C');
INSERT INTO cupboard  values ('4', '2', 'Armoire de droite');
INSERT INTO cupboard  values ('5', '2', 'Armoire de gauche');
INSERT INTO cupboard  values ('6', '4', 'Armoire principale');
INSERT INTO cupboard  values ('7', '5', 'Armoire métallique');
INSERT INTO cupboard  values ('8', '5', 'Armoire à poisons');
#
# Structure de la table productByCautionaryAdvice
#
CREATE TABLE `productbycautionaryadvice` (
  `id_chimicalProduct` bigint(20) unsigned NOT NULL,
  `id_cautionaryAdvice` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_chimicalProduct`,`id_cautionaryAdvice`),
  KEY `FK_productByCautionaryAdvice_cautionaryAdvice` (`id_cautionaryAdvice`),
  CONSTRAINT `FK_productByCautionaryAdvice_cautionaryAdvice` FOREIGN KEY (`id_cautionaryAdvice`) REFERENCES `cautionaryadvice` (`id_cautionaryAdvice`),
  CONSTRAINT `FK_productByCautionaryAdvice_chimicalProduct` FOREIGN KEY (`id_chimicalProduct`) REFERENCES `chimicalproduct` (`id_chimicalProduct`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de productByCautionaryAdvice
#
INSERT INTO productByCautionaryAdvice  values ('1', '9');
INSERT INTO productByCautionaryAdvice  values ('1', '10');
INSERT INTO productByCautionaryAdvice  values ('1', '14');
INSERT INTO productByCautionaryAdvice  values ('1', '29');
INSERT INTO productByCautionaryAdvice  values ('1', '34');
INSERT INTO productByCautionaryAdvice  values ('1', '35');
INSERT INTO productByCautionaryAdvice  values ('1', '41');
INSERT INTO productByCautionaryAdvice  values ('1', '45');
INSERT INTO productByCautionaryAdvice  values ('1', '46');
INSERT INTO productByCautionaryAdvice  values ('2', '21');
INSERT INTO productByCautionaryAdvice  values ('2', '24');
INSERT INTO productByCautionaryAdvice  values ('2', '32');
INSERT INTO productByCautionaryAdvice  values ('2', '39');
INSERT INTO productByCautionaryAdvice  values ('2', '44');
INSERT INTO productByCautionaryAdvice  values ('3', '5');
INSERT INTO productByCautionaryAdvice  values ('3', '9');
INSERT INTO productByCautionaryAdvice  values ('3', '42');
INSERT INTO productByCautionaryAdvice  values ('4', '8');
INSERT INTO productByCautionaryAdvice  values ('4', '12');
INSERT INTO productByCautionaryAdvice  values ('4', '27');
INSERT INTO productByCautionaryAdvice  values ('4', '29');
INSERT INTO productByCautionaryAdvice  values ('5', '2');
INSERT INTO productByCautionaryAdvice  values ('5', '14');
INSERT INTO productByCautionaryAdvice  values ('5', '20');
INSERT INTO productByCautionaryAdvice  values ('5', '39');
INSERT INTO productByCautionaryAdvice  values ('6', '2');
INSERT INTO productByCautionaryAdvice  values ('6', '7');
INSERT INTO productByCautionaryAdvice  values ('6', '8');
INSERT INTO productByCautionaryAdvice  values ('6', '9');
INSERT INTO productByCautionaryAdvice  values ('6', '23');
INSERT INTO productByCautionaryAdvice  values ('6', '25');
INSERT INTO productByCautionaryAdvice  values ('6', '32');
INSERT INTO productByCautionaryAdvice  values ('6', '43');
INSERT INTO productByCautionaryAdvice  values ('7', '6');
INSERT INTO productByCautionaryAdvice  values ('7', '9');
INSERT INTO productByCautionaryAdvice  values ('7', '20');
INSERT INTO productByCautionaryAdvice  values ('7', '21');
INSERT INTO productByCautionaryAdvice  values ('7', '30');
INSERT INTO productByCautionaryAdvice  values ('7', '31');
INSERT INTO productByCautionaryAdvice  values ('7', '36');
INSERT INTO productByCautionaryAdvice  values ('7', '39');
INSERT INTO productByCautionaryAdvice  values ('7', '42');
INSERT INTO productByCautionaryAdvice  values ('8', '9');
INSERT INTO productByCautionaryAdvice  values ('8', '20');
INSERT INTO productByCautionaryAdvice  values ('8', '21');
INSERT INTO productByCautionaryAdvice  values ('8', '33');
INSERT INTO productByCautionaryAdvice  values ('8', '35');
INSERT INTO productByCautionaryAdvice  values ('8', '37');
INSERT INTO productByCautionaryAdvice  values ('8', '42');
INSERT INTO productByCautionaryAdvice  values ('9', '2');
INSERT INTO productByCautionaryAdvice  values ('9', '7');
INSERT INTO productByCautionaryAdvice  values ('9', '9');
INSERT INTO productByCautionaryAdvice  values ('9', '13');
INSERT INTO productByCautionaryAdvice  values ('9', '17');
INSERT INTO productByCautionaryAdvice  values ('9', '27');
INSERT INTO productByCautionaryAdvice  values ('9', '28');
INSERT INTO productByCautionaryAdvice  values ('9', '31');
INSERT INTO productByCautionaryAdvice  values ('9', '38');
INSERT INTO productByCautionaryAdvice  values ('15', '31');
INSERT INTO productByCautionaryAdvice  values ('15', '36');
INSERT INTO productByCautionaryAdvice  values ('15', '39');
#
# Structure de la table productByDangerNote
#
CREATE TABLE `productbydangernote` (
  `id_chimicalProduct` bigint(20) unsigned NOT NULL,
  `id_dangerNote` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_chimicalProduct`,`id_dangerNote`),
  KEY `FK_productByDangerNote_dangerNote` (`id_dangerNote`),
  CONSTRAINT `FK_productByDangerNote_chimicalProduct` FOREIGN KEY (`id_chimicalProduct`) REFERENCES `chimicalproduct` (`id_chimicalProduct`),
  CONSTRAINT `FK_productByDangerNote_dangerNote` FOREIGN KEY (`id_dangerNote`) REFERENCES `dangernote` (`id_dangerNote`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de productByDangerNote
#
INSERT INTO productByDangerNote  values ('1', '1');
INSERT INTO productByDangerNote  values ('1', '15');
INSERT INTO productByDangerNote  values ('1', '35');
INSERT INTO productByDangerNote  values ('1', '58');
INSERT INTO productByDangerNote  values ('1', '92');
INSERT INTO productByDangerNote  values ('1', '104');
INSERT INTO productByDangerNote  values ('1', '107');
INSERT INTO productByDangerNote  values ('1', '111');
INSERT INTO productByDangerNote  values ('2', '13');
INSERT INTO productByDangerNote  values ('2', '40');
INSERT INTO productByDangerNote  values ('2', '62');
INSERT INTO productByDangerNote  values ('2', '63');
INSERT INTO productByDangerNote  values ('2', '74');
INSERT INTO productByDangerNote  values ('2', '111');
INSERT INTO productByDangerNote  values ('2', '118');
INSERT INTO productByDangerNote  values ('3', '7');
INSERT INTO productByDangerNote  values ('3', '11');
INSERT INTO productByDangerNote  values ('3', '21');
INSERT INTO productByDangerNote  values ('3', '93');
INSERT INTO productByDangerNote  values ('4', '38');
INSERT INTO productByDangerNote  values ('4', '40');
INSERT INTO productByDangerNote  values ('4', '56');
INSERT INTO productByDangerNote  values ('4', '101');
INSERT INTO productByDangerNote  values ('4', '115');
INSERT INTO productByDangerNote  values ('5', '19');
INSERT INTO productByDangerNote  values ('5', '21');
INSERT INTO productByDangerNote  values ('5', '22');
INSERT INTO productByDangerNote  values ('5', '30');
INSERT INTO productByDangerNote  values ('5', '46');
INSERT INTO productByDangerNote  values ('5', '47');
INSERT INTO productByDangerNote  values ('5', '83');
INSERT INTO productByDangerNote  values ('5', '88');
INSERT INTO productByDangerNote  values ('5', '95');
INSERT INTO productByDangerNote  values ('5', '105');
INSERT INTO productByDangerNote  values ('6', '36');
INSERT INTO productByDangerNote  values ('6', '37');
INSERT INTO productByDangerNote  values ('6', '39');
INSERT INTO productByDangerNote  values ('6', '41');
INSERT INTO productByDangerNote  values ('6', '71');
INSERT INTO productByDangerNote  values ('6', '82');
INSERT INTO productByDangerNote  values ('6', '100');
INSERT INTO productByDangerNote  values ('6', '110');
INSERT INTO productByDangerNote  values ('7', '3');
INSERT INTO productByDangerNote  values ('7', '7');
INSERT INTO productByDangerNote  values ('7', '38');
INSERT INTO productByDangerNote  values ('7', '72');
INSERT INTO productByDangerNote  values ('7', '80');
INSERT INTO productByDangerNote  values ('7', '108');
INSERT INTO productByDangerNote  values ('7', '114');
INSERT INTO productByDangerNote  values ('8', '25');
INSERT INTO productByDangerNote  values ('8', '78');
INSERT INTO productByDangerNote  values ('9', '20');
INSERT INTO productByDangerNote  values ('9', '40');
INSERT INTO productByDangerNote  values ('9', '43');
INSERT INTO productByDangerNote  values ('9', '48');
INSERT INTO productByDangerNote  values ('9', '102');
INSERT INTO productByDangerNote  values ('9', '106');
INSERT INTO productByDangerNote  values ('9', '116');
INSERT INTO productByDangerNote  values ('15', '24');
INSERT INTO productByDangerNote  values ('15', '32');
INSERT INTO productByDangerNote  values ('15', '33');
INSERT INTO productByDangerNote  values ('15', '88');
INSERT INTO productByDangerNote  values ('15', '90');
#
# Structure de la table productByDangerSymbol
#
CREATE TABLE `productbydangersymbol` (
  `id_chimicalProduct` bigint(20) unsigned NOT NULL,
  `id_dangerSymbol` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_chimicalProduct`,`id_dangerSymbol`),
  KEY `FK_productByDangerSymbol_dangerSymbol` (`id_dangerSymbol`),
  CONSTRAINT `FK_productByDangerSymbol_chimicalProduct` FOREIGN KEY (`id_chimicalProduct`) REFERENCES `chimicalproduct` (`id_chimicalProduct`),
  CONSTRAINT `FK_productByDangerSymbol_dangerSymbol` FOREIGN KEY (`id_dangerSymbol`) REFERENCES `dangersymbol` (`id_dangerSymbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de productByDangerSymbol
#
INSERT INTO productByDangerSymbol  values ('1', '2');
INSERT INTO productByDangerSymbol  values ('2', '4');
INSERT INTO productByDangerSymbol  values ('2', '6');
INSERT INTO productByDangerSymbol  values ('3', '5');
INSERT INTO productByDangerSymbol  values ('4', '3');
INSERT INTO productByDangerSymbol  values ('5', '1');
INSERT INTO productByDangerSymbol  values ('5', '2');
INSERT INTO productByDangerSymbol  values ('6', '7');
INSERT INTO productByDangerSymbol  values ('7', '5');
INSERT INTO productByDangerSymbol  values ('8', '8');
INSERT INTO productByDangerSymbol  values ('9', '8');
INSERT INTO productByDangerSymbol  values ('15', '4');
INSERT INTO productByDangerSymbol  values ('15', '6');
INSERT INTO productByDangerSymbol  values ('15', '8');
#
# Structure de la table productByType
#
CREATE TABLE `productbytype` (
  `id_chimicalProduct` bigint(20) unsigned NOT NULL,
  `id_type` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_chimicalProduct`,`id_type`),
  KEY `FK_productByType_type` (`id_type`),
  CONSTRAINT `FK_productByType_chimicalProduct` FOREIGN KEY (`id_chimicalProduct`) REFERENCES `chimicalproduct` (`id_chimicalProduct`),
  CONSTRAINT `FK_productByType_type` FOREIGN KEY (`id_type`) REFERENCES `type` (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de productByType
#
INSERT INTO productByType  values ('16', '10');
INSERT INTO productByType  values ('17', '11');
#
# Structure de la table shelvingUnit
#
CREATE TABLE `shelvingunit` (
  `id_shelvingUnit` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_cupboard` bigint(20) unsigned NOT NULL,
  `name_shelvingUnit` varchar(100) NOT NULL,
  PRIMARY KEY (`id_shelvingUnit`),
  UNIQUE KEY `id_shelvingUnit` (`id_shelvingUnit`),
  KEY `FK_shelvingUnit_cupboard` (`id_cupboard`),
  CONSTRAINT `FK_shelvingUnit_cupboard` FOREIGN KEY (`id_cupboard`) REFERENCES `cupboard` (`id_cupboard`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;
#
# Données de shelvingUnit
#
INSERT INTO shelvingUnit  values ('1', '1', 'Étagère n°1');
INSERT INTO shelvingUnit  values ('2', '1', 'Étagère n°2');
INSERT INTO shelvingUnit  values ('3', '1', 'Étagère n°3');
INSERT INTO shelvingUnit  values ('4', '2', 'Étagère n°1');
INSERT INTO shelvingUnit  values ('5', '2', 'Étagère n°2');
INSERT INTO shelvingUnit  values ('6', '6', 'au sol');
INSERT INTO shelvingUnit  values ('7', '6', 'en haut');
INSERT INTO shelvingUnit  values ('8', '6', 'au milieu');
INSERT INTO shelvingUnit  values ('9', '7', 'Étagère 1');
INSERT INTO shelvingUnit  values ('10', '7', 'Étagère 2');
INSERT INTO shelvingUnit  values ('11', '7', 'Étagère 3');
#
# Structure de la table storageCard
#
CREATE TABLE `storagecard` (
  `id_storageCard` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_shelvingUnit` bigint(20) unsigned NOT NULL,
  `id_chimicalProduct` bigint(20) unsigned NOT NULL,
  `id_property` bigint(20) unsigned DEFAULT NULL,
  `id_securityfile` bigint(20) unsigned DEFAULT NULL,
  `id_analysisfile` bigint(20) unsigned DEFAULT NULL,
  `id_supplier` bigint(20) unsigned DEFAULT NULL,
  `stockQuantity` int(11) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `purity` varchar(50) DEFAULT NULL,
  `serialNumber` varchar(50) DEFAULT NULL,
  `openDate` date DEFAULT NULL,
  `expirationDate` date DEFAULT NULL,
  `temperature` varchar(50) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `isArchived` tinyint(1) NOT NULL,
  `isRisked` tinyint(1) NOT NULL,
  `isPublished` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_storageCard`),
  UNIQUE KEY `id_storageCard` (`id_storageCard`),
  KEY `FK_storageCard_property` (`id_property`),
  KEY `FK_storageCard_chimicalProduct` (`id_chimicalProduct`),
  KEY `FK_storageCard_shelvingUnit` (`id_shelvingUnit`),
  KEY `FK_storageCard_supplier` (`id_supplier`),
  KEY `FK_storageCard_securityfile` (`id_securityfile`),
  KEY `FK_storageCard_analysisfile` (`id_analysisfile`),
  CONSTRAINT `FK_storageCard_analysisfile` FOREIGN KEY (`id_analysisfile`) REFERENCES `analysisfile` (`id_analysisfile`),
  CONSTRAINT `FK_storageCard_chimicalProduct` FOREIGN KEY (`id_chimicalProduct`) REFERENCES `chimicalproduct` (`id_chimicalProduct`),
  CONSTRAINT `FK_storageCard_property` FOREIGN KEY (`id_property`) REFERENCES `property` (`id_property`),
  CONSTRAINT `FK_storageCard_securityfile` FOREIGN KEY (`id_securityfile`) REFERENCES `securityfile` (`id_securityfile`),
  CONSTRAINT `FK_storageCard_shelvingUnit` FOREIGN KEY (`id_shelvingUnit`) REFERENCES `shelvingunit` (`id_shelvingUnit`),
  CONSTRAINT `FK_storageCard_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
#
# Données de storageCard
#
INSERT INTO storageCard  values ('1', '1', '9', '', '', '', '', '0', '1000', '', '', '', '', '', '', '0', '0', '1');
INSERT INTO storageCard  values ('2', '1', '10', '1', '', '', '', '450', '500', '98 %', '5416-87', '2020-10-12', '', '5-25', '', '0', '1', '1');
INSERT INTO storageCard  values ('3', '1', '11', '1', '', '', '1', '100', '100', '', '', '', '', '', '5847AR57', '0', '0', '0');
INSERT INTO storageCard  values ('4', '1', '12', '', '', '', '', '0', '500', '', '', '', '', '', '', '1', '0', '1');
INSERT INTO storageCard  values ('5', '1', '13', '', '', '', '', '500', '500', '10 mol/L', '', '', '', '', '', '0', '0', '1');
INSERT INTO storageCard  values ('6', '1', '15', '', '1', '1', '2', '250', '250', '98 %', '', '', '', '', '6046-93-1', '0', '0', '1');
#
# Structure de la table user
#
CREATE TABLE `user` (
  `id_user` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_status` bigint(20) unsigned NOT NULL,
  `id_site` bigint(20) unsigned NOT NULL,
  `username` varchar(250) NOT NULL,
  `fullName` varchar(250) NOT NULL,
  `mail` varchar(250) NOT NULL,
  `password` varchar(250) DEFAULT NULL,
  `endRightDate` date DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `id_user` (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `mail` (`mail`),
  KEY `FK_user_status` (`id_status`),
  KEY `FK_user_site` (`id_site`),
  CONSTRAINT `FK_user_site` FOREIGN KEY (`id_site`) REFERENCES `site` (`id_site`),
  CONSTRAINT `FK_user_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
#
# Données de user
#
INSERT INTO user  values ('1', '4', '4', 'Administrateur', 'Admin', 'admin@mail.fr', '$argon2i$v=19$m=65536,t=4,p=1$T1h2aHNuNjNsS1JDSmFveQ$TsxVr2vDRGo56GCYPuebbBrx/f/Yf/7opW0CINjFM2g', '');
INSERT INTO user  values ('2', '2', '4', 'isArchived', 'Utilisateur Archivé', 'archived@test.fr', '$argon2id$v=19$m=65536,t=4,p=1$V2FiZ1pIL3VTQ0R2TVFJbg$lCZrnZBFH1mjf0+1PBvom+YhYGtXHVEDDgR+3PTI5F8', '2020-09-21');
INSERT INTO user  values ('3', '1', '4', 'visiteur', 'Utilisateur visiteur', 'visiteur@test.fr', '$argon2id$v=19$m=65536,t=4,p=1$cFcxYXBGNDJrZElMUm5RMQ$HAT7Gvguwhbz6F16ymEr/0munGYGCUY1dhgTMeC0uTg', '');
INSERT INTO user  values ('4', '3', '4', 'responsable', 'Utilisateur Responsable', 'responsable@test.fr', '$argon2id$v=19$m=65536,t=4,p=1$QnJrcmFjMUdYeVpFUFFLQg$ik9La6J0MyyeplDUaGs2DJ0wWxGM9Mf7hz2FvLLGegs', '');
INSERT INTO user  values ('5', '2', '4', 'utilisateur', 'Utilisateur normal', 'utilisateur@test.fr', '$argon2id$v=19$m=65536,t=4,p=1$NnNoc25LVGR3aHNybGNWbA$1tqdkGPNCl/ByQstZzmSC8yQ3WfpT/jvgzBVV+3fkNI', '');
#
# Structure de la table movedhistory
#
CREATE TABLE `movedhistory` (
  `id_movedhistory` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_storagecard` bigint(20) unsigned NOT NULL,
  `id_shelvingunit` bigint(20) unsigned NOT NULL,
  `id_user` bigint(20) unsigned NOT NULL,
  `movedate` date NOT NULL,
  PRIMARY KEY (`id_movedhistory`),
  UNIQUE KEY `id_movedhistory` (`id_movedhistory`),
  KEY `FK_movedhistory_storagecard` (`id_storagecard`),
  KEY `FK_movedhistory_shelvingunit` (`id_shelvingunit`),
  KEY `FK_movedhistory_user` (`id_user`),
  CONSTRAINT `FK_movedhistory_shelvingunit` FOREIGN KEY (`id_shelvingunit`) REFERENCES `shelvingunit` (`id_shelvingUnit`),
  CONSTRAINT `FK_movedhistory_storagecard` FOREIGN KEY (`id_storagecard`) REFERENCES `storagecard` (`id_storageCard`) ON DELETE CASCADE,
  CONSTRAINT `FK_movedhistory_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
#
# Données de movedhistory
#
INSERT INTO movedhistory  values ('1', '1', '1', '1', '2020-10-19');
INSERT INTO movedhistory  values ('2', '2', '1', '1', '2020-10-19');
INSERT INTO movedhistory  values ('3', '3', '1', '1', '2020-10-19');
INSERT INTO movedhistory  values ('4', '4', '1', '1', '2020-10-19');
INSERT INTO movedhistory  values ('5', '5', '1', '1', '2020-10-19');
INSERT INTO movedhistory  values ('6', '6', '1', '1', '2020-10-19');
#
# Structure de la table tracability
#
CREATE TABLE `tracability` (
  `id_tracability` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` bigint(20) unsigned NOT NULL,
  `id_storageCard` bigint(20) unsigned NOT NULL,
  `retireDate` date NOT NULL,
  `retireQuantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_tracability`),
  UNIQUE KEY `id_tracability` (`id_tracability`),
  KEY `FK_tracability_user` (`id_user`),
  KEY `FK_tracability_storageCard` (`id_storageCard`),
  CONSTRAINT `FK_tracability_storageCard` FOREIGN KEY (`id_storageCard`) REFERENCES `storagecard` (`id_storageCard`),
  CONSTRAINT `FK_tracability_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de tracability
#
#
# Structure de la table controlbytype
#
CREATE TABLE `controlbytype` (
  `id_type1` bigint(20) unsigned NOT NULL,
  `id_type2` bigint(20) unsigned NOT NULL,
  `isCompatible` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_type1`,`id_type2`),
  KEY `FK_controlbytype_type2` (`id_type2`),
  CONSTRAINT `FK_controlbytype_type1` FOREIGN KEY (`id_type1`) REFERENCES `type` (`id_type`),
  CONSTRAINT `FK_controlbytype_type2` FOREIGN KEY (`id_type2`) REFERENCES `type` (`id_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
# Données de controlbytype
#
INSERT INTO controlbytype  values ('1', '1', '0');
INSERT INTO controlbytype  values ('1', '2', '0');
INSERT INTO controlbytype  values ('1', '3', '0');
INSERT INTO controlbytype  values ('1', '4', '0');
INSERT INTO controlbytype  values ('1', '5', '0');
INSERT INTO controlbytype  values ('1', '6', '0');
INSERT INTO controlbytype  values ('1', '7', '1');
INSERT INTO controlbytype  values ('1', '8', '0');
INSERT INTO controlbytype  values ('1', '9', '0');
INSERT INTO controlbytype  values ('2', '2', '1');
INSERT INTO controlbytype  values ('2', '3', '0');
INSERT INTO controlbytype  values ('2', '4', '0');
INSERT INTO controlbytype  values ('2', '5', '0');
INSERT INTO controlbytype  values ('2', '6', '0');
INSERT INTO controlbytype  values ('2', '7', '1');
INSERT INTO controlbytype  values ('2', '8', '0');
INSERT INTO controlbytype  values ('2', '9', '0');
INSERT INTO controlbytype  values ('3', '3', '1');
INSERT INTO controlbytype  values ('3', '4', '0');
INSERT INTO controlbytype  values ('3', '5', '0');
INSERT INTO controlbytype  values ('3', '6', '0');
INSERT INTO controlbytype  values ('3', '7', '0');
INSERT INTO controlbytype  values ('3', '8', '0');
INSERT INTO controlbytype  values ('3', '9', '0');
INSERT INTO controlbytype  values ('4', '4', '1');
INSERT INTO controlbytype  values ('4', '5', '0');
INSERT INTO controlbytype  values ('4', '6', '0');
INSERT INTO controlbytype  values ('4', '7', '0');
INSERT INTO controlbytype  values ('4', '8', '0');
INSERT INTO controlbytype  values ('4', '9', '0');
INSERT INTO controlbytype  values ('5', '5', '0');
INSERT INTO controlbytype  values ('5', '6', '0');
INSERT INTO controlbytype  values ('5', '7', '0');
INSERT INTO controlbytype  values ('5', '8', '0');
INSERT INTO controlbytype  values ('5', '9', '0');
INSERT INTO controlbytype  values ('6', '6', '1');
INSERT INTO controlbytype  values ('6', '7', '1');
INSERT INTO controlbytype  values ('6', '8', '1');
INSERT INTO controlbytype  values ('6', '9', '1');
INSERT INTO controlbytype  values ('7', '7', '1');
INSERT INTO controlbytype  values ('7', '8', '1');
INSERT INTO controlbytype  values ('7', '9', '1');
INSERT INTO controlbytype  values ('8', '8', '1');
INSERT INTO controlbytype  values ('8', '9', '1');
INSERT INTO controlbytype  values ('9', '9', '1');
INSERT INTO controlbytype  values ('10', '11', '0');
INSERT INTO controlbytype  values ('11', '10', '0');
#********* fin du fichier ***********