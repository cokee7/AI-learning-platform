-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Date: 2025-05-03 13:27:04
-- Server Version: 10.4.18-MariaDB
-- PHP Version: 8.0.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `isom3012`
--

-- --------------------------------------------------------

--
-- Table structure for `learning_status`
--

CREATE TABLE `learning_status` (
  `status_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `status` enum('in_progress','completed','added') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for table `learning_status`
--

INSERT INTO `learning_status` (`status_id`, `user_id`, `resource_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 52, 'completed', '2025-04-30 20:38:34', '2025-05-02 14:40:03'),
(2, 1, 47, 'in_progress', '2025-05-01 07:50:03', '2025-05-02 14:40:12'),
(3, 1, 51, 'in_progress', '2025-05-02 07:58:17', '2025-05-02 14:50:07'),
(4, 1, 50, 'completed', '2025-05-02 08:08:44', '2025-05-02 14:50:04'),
(5, 1, 48, 'in_progress', '2025-05-02 08:14:14', '2025-05-02 13:12:19'),
(6, 1, 33, '', '2025-05-02 14:18:07', '2025-05-02 14:40:07'),
(7, 1, 20, '', '2025-05-02 14:18:08', '2025-05-02 14:40:23'),
(8, 1, 21, '', '2025-05-02 14:18:11', '2025-05-02 14:39:59'),
(9, 1, 45, 'completed', '2025-05-02 14:19:12', '2025-05-02 14:40:05'),
(10, 1, 44, 'completed', '2025-05-02 14:19:23', '2025-05-02 15:36:18'),
(11, 1, 40, '', '2025-05-02 14:19:32', '2025-05-02 14:33:58'),
(12, 1, 36, 'in_progress', '2025-05-02 14:19:50', '2025-05-02 14:19:50'),
(13, 1, 43, 'in_progress', '2025-05-02 14:23:43', '2025-05-02 14:29:41'),
(14, 1, 46, '', '2025-05-02 14:23:47', '2025-05-02 14:34:00'),
(15, 1, 42, 'in_progress', '2025-05-02 14:29:40', '2025-05-02 14:29:40'),
(16, 1, 39, 'in_progress', '2025-05-02 14:29:44', '2025-05-02 14:30:49'),
(17, 1, 19, '', '2025-05-03 11:18:53', '2025-05-03 11:18:55'),
(18, 1, 31, '', '2025-05-03 11:18:57', '2025-05-03 11:18:57'),
(19, 1, 11, '', '2025-05-03 11:18:58', '2025-05-03 11:18:59'),
(20, 1, 41, '', '2025-05-03 11:19:00', '2025-05-03 11:19:00'),
(21, 1, 35, '', '2025-05-03 11:19:01', '2025-05-03 11:19:06'),
(22, 1, 2, '', '2025-05-03 11:19:02', '2025-05-03 11:19:03');

-- --------------------------------------------------------

--
-- Table structure for `resources`
--

CREATE TABLE `resources` (
  `resource_id` int(11) NOT NULL,
  `Title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `URL` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Topic` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Difficulty` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Format` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for table `resources`
--

INSERT INTO `resources` (`resource_id`, `Title`, `URL`, `Topic`, `Difficulty`, `Format`, `Source`) VALUES
(1, 'Machine Learning Specialization', 'https://www.coursera.org/specializations/machine-learning-introduction', 'Machine Learning', 'Beginner/Intermediate', 'Course', 'Coursera (Stanford/DeepLearning.AI)'),
(2, 'Deep Learning Specialization', 'https://www.coursera.org/specializations/deep-learning', 'Deep Learning/Computer Vision/Natural Language Processing', 'Intermediate', 'Course', 'Coursera (DeepLearning.AI)'),
(3, 'AI For Everyone', 'https://www.coursera.org/learn/ai-for-everyone', 'Artificial Intelligence/AI Strategy', 'Beginner', 'Course', 'Coursera (DeepLearning.AI)'),
(4, 'Natural Language Processing Specialization', 'https://www.coursera.org/specializations/natural-language-processing', 'Natural Language Processing', 'Intermediate/Advanced', 'Course', 'Coursera (DeepLearning.AI)'),
(5, 'Generative AI with Large Language Models', 'https://www.coursera.org/learn/generative-ai-with-llms', 'Natural Language Processing/Generative AI', 'Intermediate', 'Course', 'Coursera (AWS/DeepLearning.AI)'),
(6, 'CS50\'s Introduction to Artificial Intelligence with Python', 'https://www.edx.org/course/cs50s-introduction-to-artificial-intelligence-with-python', 'Artificial Intelligence/Machine Learning', 'Intermediate', 'Course', 'edX (HarvardX)'),
(7, 'Principles of Machine Learning: Python Edition', 'https://www.edx.org/course/principles-of-machine-learning-python-edition', 'Machine Learning', 'Intermediate', 'Course', 'edX (Microsoft)'),
(8, 'Machine Learning Crash Course', 'https://developers.google.com/machine-learning/crash-course', 'Machine Learning', 'Beginner/Intermediate', 'Course/Text', 'Google AI'),
(9, 'Responsible AI Practices', 'https://ai.google/responsibilities/responsible-ai-practices/', 'Artificial Intelligence/AI Ethics', 'Beginner/Intermediate', 'Text/Guidelines', 'Google AI'),
(10, 'Learn with Google AI', 'https://ai.google/learn/', 'Artificial Intelligence/Machine Learning/AI Ethics', 'Various', 'Mixed', 'Google AI'),
(11, 'CS231n: Convolutional Neural Networks for Visual Recognition', 'http://cs231n.stanford.edu/', 'Computer Vision/Deep Learning', 'Intermediate/Advanced', 'Course Materials/Videos', 'Stanford University'),
(12, 'CS224n: Natural Language Processing with Deep Learning', 'http://web.stanford.edu/class/cs224n/', 'Natural Language Processing/Deep Learning', 'Intermediate/Advanced', 'Course Materials/Videos', 'Stanford University'),
(13, 'CS229: Machine Learning', 'https://cs229.stanford.edu/', 'Machine Learning', 'Intermediate/Advanced', 'Course Materials/Notes', 'Stanford University'),
(14, 'CS234: Reinforcement Learning', 'https://web.stanford.edu/class/cs234/index.html', 'Reinforcement Learning', 'Intermediate/Advanced', 'Course Materials/Videos', 'Stanford University'),
(15, '6.S191: Introduction to Deep Learning', 'http://introtodeeplearning.com/', 'Deep Learning/Machine Learning', 'Intermediate', 'Course Materials/Videos', 'MIT'),
(16, '6.036: Introduction to Machine Learning', 'https://ocw.mit.edu/courses/6-036-introduction-to-machine-learning-fall-2020/', 'Machine Learning', 'Intermediate', 'Course Materials', 'MIT OCW'),
(17, 'Practical Deep Learning for Coders', 'https://course.fast.ai/', 'Deep Learning/Computer Vision/Natural Language Processing', 'Intermediate', 'Course/Videos/Book', 'fast.ai'),
(18, 'Hugging Face NLP Course', 'https://huggingface.co/course/chapter1/1', 'Natural Language Processing', 'Intermediate', 'Course/Text', 'Hugging Face'),
(19, 'Hugging Face Diffusion Models Course', 'https://huggingface.co/learn/diffusion-models', 'Computer Vision/Generative AI', 'Intermediate/Advanced', 'Course/Text', 'Hugging Face'),
(20, 'PyTorch Tutorials', 'https://pytorch.org/tutorials/', 'Deep Learning/ML Implementation', 'Beginner/Intermediate', 'Tutorials', 'PyTorch Official'),
(21, 'TensorFlow Tutorials', 'https://www.tensorflow.org/tutorials', 'Deep Learning/ML Implementation', 'Beginner/Intermediate', 'Tutorials', 'TensorFlow Official'),
(22, 'The Illustrated Transformer', 'http://jalammar.github.io/illustrated-transformer/', 'Natural Language Processing/Deep Learning', 'Intermediate', 'Blog Post', 'Jay Alammar Blog'),
(23, 'Understanding LSTM Networks', 'https://colah.github.io/posts/2015-08-Understanding-LSTMs/', 'Deep Learning/Natural Language Processing', 'Intermediate/Advanced', 'Blog Post', 'Christopher Olah\'s Blog'),
(24, 'Attention and Augmented Recurrent Neural Networks', 'https://distill.pub/2016/augmented-rnns/', 'Natural Language Processing/Deep Learning', 'Advanced', 'Article', 'Distill.pub'),
(25, 'Visualizing Attention (Seq2Seq Models)', 'https://jalammar.github.io/visualizing-neural-machine-translation-mechanics-of-seq2seq-models-with-attention/', 'Natural Language Processing/Deep Learning', 'Intermediate/Advanced', 'Blog Post', 'Jay Alammar Blog'),
(26, 'Yes you should understand backprop', 'https://karpathy.github.io/2019/04/25/recipe/', 'Deep Learning/Machine Learning Foundations', 'Intermediate', 'Blog Post', 'Andrej Karpathy Blog'),
(27, 'The Unreasonable Effectiveness of Recurrent Neural Networks', 'http://karpathy.github.io/2015/05/21/rnn-effectiveness/', 'Natural Language Processing/Deep Learning', 'Intermediate', 'Blog Post', 'Andrej Karpathy Blog'),
(28, 'Neural Networks and Deep Learning (Online Book)', 'http://neuralnetworksanddeeplearning.com/', 'Deep Learning/Machine Learning', 'Intermediate', 'Online Book', 'Michael Nielsen'),
(29, 'An Introduction to Statistical Learning (ISLR)', 'https://www.statlearning.com/', 'Machine Learning/Statistics', 'Intermediate', 'Book PDF/Website', 'Book (James, Witten, Hastie, Tibshirani)'),
(30, 'Deep Learning Book', 'https://www.deeplearningbook.org/', 'Deep Learning/Machine Learning/Mathematics', 'Advanced', 'Book PDF/Website', 'Book (Goodfellow, Bengio, Courville)'),
(31, 'Dive into Deep Learning', 'https://d2l.ai/', 'Deep Learning/Machine Learning/Computer Vision/Natural Language Processing', 'Intermediate/Advanced', 'Online Book/Code', 'Book (Aston Zhang et al.)'),
(32, 'Mathematics for Machine Learning', 'https://mml-book.github.io/', 'Mathematics/Machine Learning Foundations', 'Intermediate', 'Book PDF/Website', 'Book (Deisenroth, Faisal, Ong)'),
(33, '3Blue1Brown Neural Networks series', 'https://www.youtube.com/playlist?list=PLZHQObOWTQDNU6R1_67000Dx_ZCJB-3pi', 'Deep Learning/Machine Learning Foundations', 'Beginner/Intermediate', 'Videos', 'YouTube (3Blue1Brown)'),
(34, 'StatQuest with Josh Starmer (ML Playlist)', 'https://www.youtube.com/playlist?list=PLblh5JKOoLUICTaGLRoHQDuF_7q2GfuJF', 'Machine Learning/Statistics', 'Beginner/Intermediate', 'Videos', 'YouTube (StatQuest)'),
(35, 'Yannic Kilcher (Paper Reviews)', 'https://www.youtube.com/c/YannicKilcher', 'Artificial Intelligence/Machine Learning/Natural Language Processing/Computer Vision', 'Advanced', 'Videos', 'YouTube (Yannic Kilcher)'),
(36, 'Krish Naik (ML/DL/Deployment Tutorials)', 'https://www.youtube.com/user/krishnaik06', 'Machine Learning/Deep Learning/Machine Learning Operations', 'Intermediate', 'Videos', 'YouTube (Krish Naik)'),
(37, 'Lex Fridman Podcast', 'https://www.youtube.com/c/lexfridman', 'Artificial Intelligence/Artificial General Intelligence', 'Various', 'Videos/Podcast', 'YouTube (Lex Fridman)'),
(38, '动手学深度学习 - 李沐 (知乎专栏/文章)', 'https://zhuanlan.zhihu.com/c_106982670', 'Artificial Intelligence/Computer Vision/Machine Learning', 'Intermediate/Advanced', 'Zhihu Column', '李沐 (Mu Li)'),
(39, '动手学深度学习 - 李沐 (B站视频)', 'https://space.bilibili.com/1567748478/channel/seriesdetail?sid=358497', 'Artificial Intelligence/Computer Vision/Machine Learning', 'Intermediate/Advanced', 'Videos', 'Bilibili (李沐/Aston Zhang et al.)'),
(40, '吴恩达机器学习课程 (中文字幕搬运)', 'https://www.bilibili.com/video/BV164411b7dx/', 'Reinforcement Learning', 'Beginner/Intermediate', 'Videos', 'Bilibili (搬运/吴恩达)'),
(41, '王木头学科学 (AI科普)', 'https://space.bilibili.com/490818261', 'Artificial Intelligence/Machine Learning/Natural Language Processing/Computer Vision/Generative AI', 'Various', 'Videos', 'Bilibili (王木头学科学)'),
(42, '同济子豪兄 (AI实战/教程)', 'https://space.bilibili.com/1900783', 'Natural Language Processing/Generative AI', 'Intermediate', 'Videos', 'Bilibili (同济子豪兄)'),
(43, 'Spinning Up in Deep RL', 'https://spinningup.openai.com/en/latest/', 'Artificial Intelligence', 'Intermediate/Advanced', 'Course/Text', 'OpenAI'),
(44, 'OpenAI Blog', 'https://openai.com/blog/', 'Machine Learning/Python (Programming Language)/Structured Query Language/Visualization', 'Various', 'Blog', 'OpenAI'),
(45, 'GPT Best Practices', 'https://platform.openai.com/docs/guides/gpt-best-practices', 'Deep Learning/Machine Learning/Data Visualization', 'Intermediate', 'Documentation', 'OpenAI'),
(46, 'Elements of AI', 'https://www.elementsofai.com/', 'Machine Learning Operations', 'Various', 'Community/Podcast/Blog', 'MLOps Community'),
(47, 'Kaggle Learn (Micro-courses)', 'https://www.kaggle.com/learn', 'Artificial Intelligence/Machine Learning/Natural Language Processing/Computer Vision/Reinforcement L', 'Intermediate', 'Course/Text', 'Goku Mohandas'),
(48, 'Distill.pub (Archive)', 'https://distill.pub/', 'Machine Learning/Machine Learning Operations', 'Intermediate', 'Course/Text', 'Goku Mohandas'),
(49, 'MLOps Community', 'https://mlops.community/', 'Deep Learning/Machine Learning Operations', 'Intermediate/Advanced', 'Course Materials/Community', 'Community Course'),
(50, 'Papers With Code', 'https://paperswithcode.com/', 'Mathematics/Machine Learning Foundations', 'Intermediate', 'Book PDF/Website', 'Book (Deisenroth, Faisal, Ong)'),
(51, 'Made With ML (MLOps Course)', 'https://madewithml.com/#course', 'Deep Learning/Machine Learning Foundations', 'Beginner/Intermediate', 'Videos', 'YouTube (3Blue1Brown)'),
(52, 'Full Stack Deep Learning', 'https://fullstackdeeplearning.com/', 'Machine Learning/Deep Learning/Machine Learning Operations', 'Intermediate/Advanced', 'Online Book/Code', 'Book (Aston Zhang et al.)');

-- --------------------------------------------------------

--
-- Table structure for `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `registration_date`) VALUES
(1, 'coke', '$2y$10$0KhpwG.J4ZX1o2r0RaKuEeXnfgwna.sFzKyXtEO/JSFckSLXYJiQy', 'ljy1439642696@gmail.com', '2025-04-28 12:48:53');

-- --------------------------------------------------------

--
-- Table structure for `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `interested_topics` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skill_level` enum('Beginner','Intermediate','Advanced','Various') COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `interested_topics`, `skill_level`) VALUES
(1, 1, 'Computer Vision,Data Visualization', 'Various');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `learning_status`
--
ALTER TABLE `learning_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `user_resource_unique` (`user_id`,`resource_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`resource_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `learning_status`
--
ALTER TABLE `learning_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `learning_status`
--
ALTER TABLE `learning_status`
  ADD CONSTRAINT `learning_status_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_status_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`resource_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
