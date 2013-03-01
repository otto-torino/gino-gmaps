--
-- Table structure for table `gmaps_grp`
--

CREATE TABLE IF NOT EXISTS `gmaps_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `gmaps_grp`
--

INSERT INTO `gmaps_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi. Amministrazione completa del modulo.', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_map`
--

CREATE TABLE IF NOT EXISTS `gmaps_map` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `width` varchar(32) NOT NULL,
  `height` varchar(32) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_map_point`
--

CREATE TABLE IF NOT EXISTS `gmaps_map_point` (
  `map_id` int(11) NOT NULL,
  `point_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_map_polygon`
--

CREATE TABLE IF NOT EXISTS `gmaps_map_polygon` (
  `map_id` int(11) NOT NULL,
  `polygon_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_map_polyline`
--

CREATE TABLE IF NOT EXISTS `gmaps_map_polyline` (
  `map_id` int(11) NOT NULL,
  `polyline_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_marker`
--

CREATE TABLE IF NOT EXISTS `gmaps_marker` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `icon` varchar(200) NOT NULL,
  `shadow` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_marker`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_opt`
--

CREATE TABLE IF NOT EXISTS `gmaps_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(255) default NULL,
  `default_map` int(11) NOT NULL,
  `list_fields` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point`
--

CREATE TABLE IF NOT EXISTS `gmaps_point` (
  `id` int(11) NOT NULL auto_increment,
  `insert_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `instance` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `cap` varchar(16) NOT NULL,
  `city` varchar(200) NOT NULL,
  `nation` int(11) NOT NULL,
  `address` varchar(200) NOT NULL,
  `marker` int(11) default NULL,
  `description` text,
  `information` text,
  `phone` varchar(32) default NULL,
  `email` varchar(64) default NULL,
  `web` varchar(200) default NULL,
  `opening_hours` text,
  `lat` varchar(128) NOT NULL,
  `lng` varchar(128) NOT NULL,
  `updating` int(1) NOT NULL,
  `updating_email` varchar(64) default NULL,
  `updating_code` varchar(32) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_attachment`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_attachment` (
  `id` int(11) NOT NULL auto_increment,
  `point_id` int(11) NOT NULL,
  `insert_date` datetime NOT NULL,
  `name` varchar(200) NOT NULL,
  `size` int(8) NOT NULL,
  `description` text,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_attachment`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_collection`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_collection` (
  `id` int(11) NOT NULL auto_increment,
  `point_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_collection`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_collection_image`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_collection_image` (
  `id` int(11) NOT NULL auto_increment,
  `collection_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `credits` varchar(128) default NULL,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_collection_image`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_ctg` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_event`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_event` (
  `id` int(11) NOT NULL auto_increment,
  `point_id` int(11) NOT NULL,
  `insert_date` datetime NOT NULL,
  `date` date NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `duration` varchar(200) NOT NULL,
  `image` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_event`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_image`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_image` (
  `id` int(11) NOT NULL auto_increment,
  `point_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `credits` varchar(128) default NULL,
  `filename` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_image`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_point_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_point_ctg` (
  `point_id` int(11) NOT NULL,
  `ctg_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_point_point_ctg`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_service`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_service` (
  `point_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_point_service`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_point_video`
--

CREATE TABLE IF NOT EXISTS `gmaps_point_video` (
  `id` int(11) NOT NULL auto_increment,
  `point_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `credits` varchar(200) default NULL,
  `code` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_point_video`
--


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polygon`
--

CREATE TABLE IF NOT EXISTS `gmaps_polygon` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text,
  `lat` varchar(255) NOT NULL,
  `lng` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL,
  `width` int(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polygon_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_polygon_ctg` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polygon_point`
--

CREATE TABLE IF NOT EXISTS `gmaps_polygon_point` (
  `polygon_id` int(11) NOT NULL,
  `point_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_polygon_point`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polygon_polygon_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_polygon_polygon_ctg` (
  `polygon_id` int(11) NOT NULL,
  `ctg_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_polygon_polygon_ctg`
--

INSERT INTO `gmaps_polygon_polygon_ctg` (`polygon_id`, `ctg_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polyline`
--

CREATE TABLE IF NOT EXISTS `gmaps_polyline` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text,
  `lat` varchar(255) NOT NULL,
  `lng` varchar(255) NOT NULL,
  `color` varchar(7) NOT NULL,
  `width` int(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polyline_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_polyline_ctg` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_polyline_ctg`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polyline_point`
--

CREATE TABLE IF NOT EXISTS `gmaps_polyline_point` (
  `polyline_id` int(11) NOT NULL,
  `point_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_polyline_point`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_polyline_polyline_ctg`
--

CREATE TABLE IF NOT EXISTS `gmaps_polyline_polyline_ctg` (
  `polyline_id` int(11) NOT NULL,
  `ctg_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_polyline_polyline_ctg`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_service`
--

CREATE TABLE IF NOT EXISTS `gmaps_service` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `icon` varchar(200) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `gmaps_service`
--

-- --------------------------------------------------------

--
-- Table structure for table `gmaps_usr`
--

CREATE TABLE IF NOT EXISTS `gmaps_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `gmaps_usr`
--


