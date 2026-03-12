-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 28, 2025 lúc 03:45 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shopdb`
--
CREATE DATABASE IF NOT EXISTS `shopdb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `shopdb`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `books`
--

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `published_year` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `price`, `sale_price`, `description`, `published_year`, `image`) VALUES
(1, '吾輩は猫である', '夏目漱石', 1200, 1020.00, '夏目漱石の代表作。ユーモラスで風刺の効いた名作。', 1905, 'neko.jpg'),
(2, 'こころ', '夏目漱石', 1500, 1275.00, '人間の心の葛藤を描いた名作。', 1914, 'kokoro.jpg'),
(3, '銀河鉄道の夜', '宮沢賢治', 1300, 1105.00, '幻想的な物語で、子供にも大人にも愛されています。', 1934, 'ginga.jpg'),
(4, '人間失格', '太宰治', 1600, NULL, '太宰治の代表作。深い絶望と孤独を描く。', 1948, 'ningenshikkaku.jpg'),
(5, 'ノルウェイの森', '村上春樹', 2000, NULL, '青春の痛みと喪失を描いた感動作。', 1987, 'norway.jpg'),
(6, '雪国', '川端康成', 1400, 1190.00, 'ノーベル文学賞作家の代表作。', 1937, 'yukiguni.jpg'),
(10, '1Q84', '村上春樹', 2500, 2125.00, '並行世界を舞台にした壮大な物語。', 2009, '1q84.jpg'),
(12, '羅生門', '芥川龍之介', 1200, 1020.00, '人間のエゴと倫理を描いた短編小説。', 1915, 'rashomon.jpg'),
(13, '雪の女王', 'アンデルセン', 1100, NULL, '幻想的な童話、友情と冒険の物語。', 1844, 'yukionna.jpg'),
(14, 'アルケミスト', 'パウロ・コエーリョ', 1500, NULL, '夢を追い求める少年の旅物語。', 1988, 'alchemist.jpg'),
(15, '時をかける少女', '筒井康隆', 1300, NULL, '時間旅行を題材にした青春SF小説。', 1967, 'toki.jpg'),
(16, '吾輩は猫である 2', '夏目漱石', 1200, NULL, '猫の視点から描かれる社会風刺の続編。', 1906, 'neko2.jpg'),
(17, '火車', '宮部みゆき', 1600, NULL, '現代社会の闇と人間ドラマを描く推理小説。', 1992, 'kasha.jpg'),
(18, '博士の愛した数式', '小川洋子', 1400, 1190.00, '数学と人間の温かい心を描いた感動作。', 2003, 'hakase.jpg'),
(19, 'カラマーゾフの兄弟', 'ドストエフスキー', 2000, NULL, '人間の罪と愛、哲学を描いた長編小説。', 1880, 'karamazov.jpg'),
(22, '走れメロス', '太宰治', 1100, NULL, '友情と信念を描いた短編小説。', 1940, 'hashiremelos.jpg'),
(23, '海辺のカフカ', '村上春樹', 2000, NULL, '現実と幻想が交錯する長編小説。', 2002, 'umibenokafka.jpg'),
(24, 'ブラックジャック', '手塚治虫', 1500, NULL, '天才外科医ブラックジャックの物語。', 1973, 'blackjack.jpg'),
(25, '銀河英雄伝説', '田中芳樹', 1800, 1530.00, '銀河帝国と自由惑星同盟の戦争物語。', 1982, 'gingaheroes.jpg'),
(27, '君の膵臓をたべたい', '住野よる', 1400, 1190.00, '青春と命の儚さを描く感動作。', 2015, 'kiminosui.jpg'),
(30, '銀河鉄道999', '松本零士', 1400, NULL, '銀河を旅する少年の冒険漫画。', 1977, 'ginga999.jpg'),
(34, '銀河鉄道の夜 2', '宮沢賢治', 1300, NULL, '幻想的な冒険の続編。', 1935, 'ginga2.jpg'),
(35, '吾輩は猫である 3', '夏目漱石', 1200, NULL, '猫の視点から描く社会風刺の三部作。', 1907, 'neko3.jpg'),
(37, 'アンドロイドは電気羊の夢を見るか', 'フィリップ・K・ディック', 1800, NULL, '近未来SFの名作小説。', 1968, 'android.jpg'),
(38, 'ノルウェイの森 2', '村上春樹', 2000, NULL, '青春と喪失を描いた続編。', 1988, 'norway2.jpg'),
(39, 'ワンピース 1', '尾田栄一郎', 1300, NULL, '海賊たちの冒険漫画第一巻。', 1997, 'onepiece1.jpg'),
(40, 'ワンピース 2', '尾田栄一郎', 1300, 1105.00, '冒険と友情の続き。', 1997, 'onepiece2.jpg'),
(41, '注文の多い料理店', '宮沢賢治', 1100, 935.00, '二人の紳士が山奥で見つけた奇妙な西洋料理店での不思議な体験を描く短編集。', 1924, 'chumon_ryoriten.jpg'),
(42, '雨ニモマケズ', '宮沢賢治', 1000, 850.00, '有名な詩。質素で丈夫な生き方への願いが込められている。', 1931, 'amenimo_makezu.jpg'),
(43, 'セロ弾きのゴーシュ', '宮沢賢治', 1100, NULL, 'うまくチェロが弾けないゴーシュが、動物たちとの交流を通して成長する物語。', 1934, 'cello_gauche.jpg'),
(44, '斜陽', '太宰治', 1400, NULL, '戦後の没落貴族の母と娘の生き様を描いた太宰治の代表作の一つ。', 1947, 'shayo.jpg'),
(45, 'ヴィヨンの妻', '太宰治', 1200, NULL, '破滅的な生活を送る詩人の夫を持つ妻の視点から描かれる物語。', 1947, 'viyon_tsuma.jpg'),
(46, '女生徒', '太宰治', 1100, NULL, 'ある女学生の一日の心の動きを繊細に描いた短編小説。', 1939, 'joseito.jpg'),
(47, '伊豆の踊子', '川端康成', 1200, NULL, '伊豆を旅する学生と旅芸人の踊り子との淡い出会いを描いた初期の名作。', 1926, 'izu_odoriko.jpg'),
(48, '千羽鶴', '川端康成', 1400, 1190.00, '茶道の世界を背景に、複雑な人間関係と愛憎を描く。', 1952, 'senbazuru.jpg'),
(49, '眠れる美女', '川端康成', 1300, NULL, '老人が眠っている若い女性のそばで過ごす秘密の宿を描いた幻想的な作品。', 1961, 'nemureru_bijo.jpg'),
(50, '古都', '川端康成', 1400, NULL, '京都を舞台に、生き別れた双子の姉妹の運命を描く。', 1962, 'koto.jpg'),
(51, '鼻', '芥川龍之介', 1100, NULL, '長い鼻を持つ僧侶の内面の苦悩と虚栄心を描いた初期の短編。', 1916, 'hana.jpg'),
(52, '蜘蛛の糸', '芥川龍之介', 1000, NULL, '地獄に落ちた罪人が一本の蜘蛛の糸によって救われるチャンスを得る仏教説話に基づく短編。', 1918, 'kumo_no_ito.jpg'),
(53, '地獄変', '芥川龍之介', 1200, NULL, '地獄の絵を描くために狂気に陥る絵師の物語。芸術と倫理の葛藤を描く。', 1918, 'jigokuhen.jpg'),
(54, '河童', '芥川龍之介', 1300, 1105.00, '精神病院の患者が語る、河童の世界での体験を通じた人間社会への風刺。', 1927, 'kappa.jpg'),
(55, '人魚姫', 'アンデルセン', 1000, NULL, '人間になりたいと願う人魚の王女の悲しくも美しい物語。', 1837, 'ningyohime.jpg'),
(56, 'みにくいアヒルの子', 'アンデルセン', 1000, NULL, '周囲からいじめられるアヒルの子が、実は美しい白鳥だったという自己発見の物語。', 1843, 'minikui_ahirunoko.jpg'),
(57, 'マッチ売りの少女', 'アンデルセン', 1000, 850.00, '大晦日の夜、寒さの中でマッチを擦りながら幻を見る少女の悲しい物語。', 1845, 'matchi_uri.jpg'),
(58, '裸の王様', 'アンデルセン', 1000, NULL, '見えない服を着せられた王様の行列を描き、虚栄心や社会の愚かさを風刺する物語。', 1837, 'hadaka_osama.jpg'),
(59, 'ベロニカは死ぬことにした', 'パウロ・コエーリョ', 1600, NULL, '自殺を図った若い女性ベロニカが精神病院で生の意味を見出す物語。', 1998, 'veronika.jpg'),
(60, '星の巡礼', 'パウロ・コエーリョ', 1500, NULL, 'スペインのサンティアゴ・デ・コンポステーラの巡礼路での精神的な旅を描く。', 1987, 'hoshi_junrei.jpg'),
(61, '11分間', 'パウロ・コエーリョ', 1600, NULL, '愛と性を探求するブラジル人女性マリアのヨーロッパでの体験を描く。', 2003, 'juuippunkan.jpg'),
(62, 'ピエドラ川のほとりで私は泣いた', 'パウロ・コエーリョ', 1500, NULL, '再会した幼なじみとの旅を通して、愛と信仰、自己発見を描く物語。', 1994, 'piedra_gawa.jpg'),
(63, 'パプリカ', '筒井康隆', 1700, NULL, '他人の夢に入り込む夢探偵パプリカの活躍を描くSF小説。', 1993, 'paprika.jpg'),
(64, '富豪刑事', '筒井康隆', 1400, 1190.00, '大富豪の刑事が莫大な資産を使って事件を解決するユーモラスなミステリー。', 1978, 'fugo_keiji.jpg'),
(65, '家族八景', '筒井康隆', 1300, 1105.00, '人の心が読める家政婦が見た様々な家庭の秘密を描く短編集。', 1972, 'kazoku_hakkei.jpg'),
(66, '日本以外全部沈没', '筒井康隆', 1400, NULL, '日本列島だけを残して世界の大陸が沈没した後の混乱を描くSFパロディ。', 1973, 'nihon_igai.jpg'),
(67, '模倣犯', '宮部みゆき', 2200, 1870.00, '連続誘拐殺人事件を巡る、被害者、加害者、マスコミ、警察の視点から描く長編ミステリー。', 2001, 'mohohan.jpg'),
(68, '理由', '宮部みゆき', 1600, NULL, '高級マンションで起きた一家惨殺事件の真相を、多くの関係者の証言から解き明かす。', 1998, 'riyu.jpg'),
(69, 'ブレイブ・ストーリー', '宮部みゆき', 1800, NULL, '家庭の問題を抱える少年ワタルが、運命を変えるために異世界「幻界」へ旅立つ冒険ファンタジー。', 2003, 'brave_story.jpg'),
(70, 'ソロモンの偽証', '宮部みゆき', 2000, NULL, '中学校で起きた生徒の転落死を巡り、生徒たちが自分たちで真相を究明しようと学校内裁判を開く物語。', 2012, 'solomon_gisho.jpg'),
(71, '妊娠カレンダー', '小川洋子', 1300, NULL, '妊娠した姉の様子を妹が静かに観察する日記形式の物語を含む短編集。芥川賞受賞作。', 1991, 'ninshin_calendar.jpg'),
(72, '密やかな結晶', '小川洋子', 1500, NULL, '様々なものが次々と消滅していく島で、記憶を守ろうとする人々の物語。', 1994, 'hisoyaka_kessho.jpg'),
(73, '薬指の標本', '小川洋子', 1400, NULL, '思い出の品々を標本にする奇妙な研究所で働く女性の物語。', 1994, 'kusuriyubi_hyohon.jpg'),
(74, '猫を抱いて象と泳ぐ', '小川洋子', 1600, 1360.00, 'チェスで駒を動かすために箱の中に隠れる少年リトル・アリョーヒンの数奇な運命。', 2009, 'neko_daite.jpg'),
(75, '罪と罰', 'ドストエフスキー', 1800, 1530.00, '貧しい青年ラスコーリニコフが犯した殺人と思想的葛藤、そして再生を描く長編小説。', 1866, 'tsumi_batsu.jpg'),
(76, '白痴', 'ドストエフスキー', 1800, 1530.00, '純粋で善良なムイシュキン公爵が、ロシア社会の欲望や偽善の中で翻弄される物語。', 1869, 'hakuchi.jpg'),
(77, '悪霊', 'ドストエフスキー', 1900, 1615.00, '19世紀ロシアの虚無主義的な革命思想家たちの活動とその破滅を描く。', 1872, 'akuryo.jpg'),
(78, '地下室の手記', 'ドストエフスキー', 1400, 1190.00, '社会から孤立した「地下生活者」の屈折した内面と思想を独白形式で描く。', 1864, 'chikashitsu_shuki.jpg'),
(79, '火の鳥', '手塚治虫', 1800, NULL, '永遠の命を与える火の鳥を巡り、古代から未来までの壮大な人間の生と死を描くライフワーク。', 1967, 'hi_no_tori.jpg'),
(80, '鉄腕アトム', '手塚治虫', 1200, NULL, '10万馬力の力を持つ心優しい少年ロボット、アトムの活躍を描くSF漫画の金字塔。', 1952, 'atom.jpg'),
(81, 'ジャングル大帝', '手塚治虫', 1300, 1105.00, 'アフリカのジャングルを舞台に、白いライオンの子レオ（キンバ）の成長と冒険を描く物語。', 1950, 'jungle_taitei.jpg'),
(82, 'ブッダ', '手塚治虫', 1600, NULL, '仏陀（シッダールタ）の生涯を、手塚治虫独自の解釈とドラマティックな展開で描く大河漫画。', 1972, 'buddha.jpg'),
(83, 'アルスラーン戦記', '田中芳樹', 1500, NULL, '異教徒に国を奪われたパルス国の王子アルスラーンが、仲間と共に王都奪還を目指す壮大な英雄譚。', 1986, 'arslan_senki.jpg'),
(84, '創竜伝', '田中芳樹', 1400, NULL, '現代社会に生きる竜族の末裔である四人兄弟が、巨大な陰謀に立ち向かう伝奇アクション。', 1987, 'soryuden.jpg'),
(85, 'タイタニア', '田中芳樹', 1600, NULL, '宇宙に進出した人類社会で、絶大な力を持つ一族タイタニアの興亡を描くスペースオペラ。', 1988, 'tytania.jpg'),
(86, '薬師寺涼子の怪奇事件簿', '田中芳樹', 1400, NULL, '美貌と才能、そして破天荒な性格を持つ警視庁のエリート警視、薬師寺涼子が怪奇事件に挑む。', 1996, 'yakushiji_ryoko.jpg'),
(87, 'また、同じ夢を見ていた', '住野よる', 1400, NULL, '友達のいない少女が、風変わりな女性たちとの出会いを通して「幸せとは何か」を学ぶ物語。', 2016, 'mata_onaji_yume.jpg'),
(88, 'よるのばけもの', '住野よる', 1400, NULL, '夜になると化け物に変身してしまう少年と、クラスでいじめられている少女の秘密の交流。', 2016, 'yoru_no_bakemono.jpg'),
(89, 'か「」く「」し「」ご「」と「', '住野よる', 1400, NULL, 'それぞれ秘密を抱える高校生たちの少し歪んだ青春と関係性を描く。', 2017, 'kakushigoto.jpg'),
(90, '青くて痛くて脆い', '住野よる', 1500, NULL, '大学で出会った二人が立ち上げた秘密結社。時を経て変化していく関係性と青春の痛み。', 2018, 'aokute_itakute_moroi.jpg'),
(91, '宇宙戦艦ヤマト', '松本零士', 1400, 1190.00, '滅亡の危機にある地球を救うため、宇宙戦艦ヤマトがイスカンダルへの旅に出るSFアニメの金字塔。', 1974, 'yamato.jpg'),
(92, 'クイーン・エメラルダス', '松本零士', 1300, NULL, '謎多き宇宙海賊の女性エメラルダスの物語。ハーロックや999とも世界観を共有する。', 1978, 'emeraldas.jpg'),
(93, '宇宙海賊キャプテンハーロック', '松本零士', 1400, NULL, '腐敗した地球政府に反旗を翻し、自由のために戦う宇宙海賊ハーロックとアルカディア号の物語。', 1977, 'harlock.jpg'),
(94, '男おいどん', '松本零士', 1200, 1020.00, '四畳半の下宿で暮らす貧乏浪人生、大山昇太（おいどん）の日常をユーモラスに描く。', 1971, 'otoko_oidon.jpg'),
(96, 'マイノリティ・リポート', 'フィリップ・K・ディック', 1400, NULL, '犯罪予知システムをテーマにした表題作を含む、ディックの代表的な短編を集めた一冊。', 1956, 'minority_report.jpg'),
(97, 'ユービック', 'フィリップ・K・ディック', 1700, NULL, '現実と認識が崩壊していく中で、謎の物質「ユービック」の正体を探るSFサスペンス。', 1969, 'ubik.jpg'),
(98, '流れよ我が涙、と警官は言った', 'フィリップ・K・ディック', 1800, 1530.00, 'ある日突然、自分の存在が抹消された世界に放り込まれた有名人の逃亡劇を描く。', 1974, 'nagareyo_namida.jpg'),
(99, 'ねじまき鳥クロニクル', '村上春樹', 2000, NULL, '失踪した猫と妻を探す男が、東京の奇妙な地下世界へと導かれる、複雑でシュールな物語。', 1994, 'nejimaki_dori.jpg'),
(100, 'ワンピース 3', '尾田栄一郎', 1300, NULL, '偽れぬもの。ウソップ登場！ルフィたちの冒険は続く。', 1998, 'onepiece3.jpg'),
(101, 'ワンピース 4', '尾田栄一郎', 1300, NULL, '新月。海上レストラン・バラティエでの戦いが始まる。', 1998, 'onepiece4.jpg'),
(102, 'ワンピース 5', '尾田栄一郎', 1300, NULL, '誰がために鐘は鳴る。サンジ加入！東の海の強敵クリークとの決戦。', 1998, 'onepiece5.jpg'),
(103, '坊っちゃん', '夏目漱石', 1300, NULL, '江戸っ子気質の青年教師が、四国の松山の中学校に赴任して体験する騒動を描いた、漱石の人気小説。', 1906, 'botchan.jpg'),
(104, '暗闇のスキャナー', 'フィリップ・K・ディック', 1700, NULL, '近未来のカリフォルニアを舞台に、薬物「物質D」の蔓延と、それを追う潜入捜査官の自己同一性の崩壊を描く。', 1977, 'scanner_darkly.jpg'),
(105, '容疑者Xの献身', '東野圭吾', 1800, NULL, '天才数学者が仕組んだ完全犯罪と、物理学者湯川学との対決を描くガリレオシリーズ最高傑作。', 2005, 'yogisha_x.jpg'),
(106, '白夜行', '東野圭吾', 2000, 1700.00, '幼少期のある事件から、互いを支え合いながらも決して交わることのない男女の19年間にわたる闇を描く。', 1999, 'byakuyako.jpg'),
(107, 'ナミヤ雑貨店の奇蹟', '東野圭吾', 1700, NULL, '廃業した雑貨店に忍び込んだ若者たちが、過去からの相談の手紙に返事を書くことで起こる奇跡の物語。', 2012, 'namiya.jpg'),
(108, '秘密', '東野圭吾', 1600, NULL, 'バス事故で妻が亡くなり、娘の体に妻の魂が宿ってしまうという奇妙な運命を描く。', 1998, 'himitsu.jpg'),
(109, 'マスカレード・ホテル', '東野圭吾', 1700, 1445.00, '高級ホテルを舞台にした連続殺人予告事件。潜入捜査官の新田浩介とフロントクラークの山岸尚美が事件に挑む。', 2011, 'masquerade_hotel.jpg'),
(110, 'ハリー・ポッターと賢者の石', 'J・K・ローリング', 1900, NULL, '孤児の少年ハリー・ポッターが魔法使いであることを知り、ホグワーツ魔法魔術学校に入学する第一巻。', 1997, 'hp_kenja_no_ishi.jpg'),
(111, 'ハリー・ポッターと秘密の部屋', 'J・K・ローリング', 1900, NULL, 'ホグワーツの二年生になったハリーが、校内で起こる謎の襲撃事件と「秘密の部屋」の伝説に挑む。', 1998, 'hp_himitsu_no_heya.jpg'),
(112, 'ハリー・ポッターとアズカバンの囚人', 'J・K・ローリング', 2100, 1785.00, '魔法牢獄アズカバンから脱獄したシリウス・ブラックがハリーの命を狙っている？シリーズ第三巻。', 1999, 'hp_azkaban.jpg'),
(113, 'ハリー・ポッターと炎のゴブレット', 'J・K・ローリング', 2500, NULL, '伝説の三大魔法学校対抗試合がホグワーツで開催される。しかし、ハリーが予期せず代表選手に選ばれてしまい…。', 2000, 'hp_hono_no_goblet.jpg'),
(114, 'ハリー・ポッターと不死鳥の騎士団', 'J・K・ローリング', 2800, NULL, '魔法界がヴォルデモートの復活を認めない中、ハリーと仲間たちは秘密組織「不死鳥の騎士団」を結成する。', 2003, 'hp_fushicho.jpg');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `order_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `shipping_address`, `order_status`, `created_at`) VALUES
(6, 1, 3300.00, '1', 'Delivered', '2025-10-17 06:33:33'),
(7, 1, 7400.00, '123456789', 'Shipped', '2025-10-22 05:56:22'),
(8, 3, 1800.00, '123456789 ssutumi', 'Pending', '2025-10-22 06:02:57');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `quantity`, `price`) VALUES
(1, 6, 23, 1, 2000),
(2, 6, 39, 1, 1300),
(3, 7, 39, 3, 1300),
(4, 7, 38, 1, 2000),
(5, 7, 2, 1, 1500),
(6, 8, 37, 1, 1800);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'lenghia', 'lenghia@gmail.com', '$2y$10$38KIAtaHfm8SEXZn5TynE.2FqkEeOf3lUa.Qwd92d7.8oButyqo2u', 'user', '2025-10-17 06:24:43'),
(2, 'admin', 'admin013579@gmail.com', '$2y$10$.4hkGgBbbCctd1K7WGUFzerhOU6mHie1d0mn0QSNgtAQnBur4rGDm', 'admin', '2025-10-22 05:22:13'),
(3, 'dewan', 'dewan@gmail.com', '$2y$10$05BUAmN08.4K/9aU.OA2fOZRHhMkiUepQcHV7K4IVQq.EywyiVhTO', 'user', '2025-10-22 06:02:32');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_book_unique` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_order_items_book` (`book_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`),
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
