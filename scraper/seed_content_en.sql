-- ─────────────────────────────────────────────────────────────────
-- Atlas of Graphic Design — English editorial content seed
-- Descriptions (description_longue) + citations
-- Angle: graphic design only (typography, poster, layout, identity)
-- To run: phpMyAdmin → nothumanatlas → Import this file
-- ─────────────────────────────────────────────────────────────────

-- ── NIVEAU 1 — Main movements ────────────────────────────────────

UPDATE `courants` SET
  `description_longue` = 'Arts & Crafts redefined the book and the printed page as sites of craft mastery. William Morris\'s Kelmscott Press, founded in 1891, revived blackletter types, hand-carved ornament, and dense decorative borders — treating typography as architecture for the eye. The movement opposed the visual degradation of industrial printing and insisted that every detail of a page, from the ink weight to the margin proportion, deserved slow, deliberate attention. Its richly decorated title pages, illuminated initials, and vine-pattern margins introduced a decorative vocabulary that fed directly into Art Nouveau. The legacy lives in every brand that chooses texture and hand-drawn detail over mechanical perfection.',
  `citation` = 'Have nothing in your house that you do not know to be useful, or believe to be beautiful.',
  `citation_auteur` = 'William Morris'
WHERE `slug` = 'arts-crafts';

UPDATE `courants` SET
  `description_longue` = 'Art Nouveau collapsed the boundary between commercial printing and fine art. Alphonse Mucha\'s Parisian theatre posters made the female form and botanical ornament inseparable from the letterform — type grew from stems, frames bloomed like petals. Jules Chéret and Toulouse-Lautrec elevated lithography to an urban art plastered across café walls. The style proved that advertising could carry genuine aesthetic ambition, and that decorative complexity was not opposed to commercial function. Art Nouveau\'s flowing line and pastel chromolithography transformed the street poster into a collector\'s object, establishing poster design as the defining graphic medium of the fin-de-siècle.',
  `citation` = 'I never made drawings for their own sake. Every one had a purpose, a commercial purpose.',
  `citation_auteur` = 'Alphonse Mucha'
WHERE `slug` = 'art-nouveau';

UPDATE `courants` SET
  `description_longue` = 'Futurism brought velocity into graphic design. Marinetti\'s typographic manifestos shattered the grid — words scattered at diagonal angles, multiple type sizes colliding on the same page, letterforms as kinetic energy rather than static communication. The movement invented what it called "words in freedom": a visual language that treated the printed page as a field of force rather than a sequence of lines. Futurist book design and propaganda posters used aggressive diagonal composition, bold contrast, and onomatopoeic typography to convey speed, noise, and machine power. Its radical page layouts directly anticipated the experimental typography of Dada, New Wave, and digital-era deconstruction.',
  `citation` = 'I am beginning to think that if typography has a future it lies in Marinetti\'s direction.',
  `citation_auteur` = 'Jan Tschichold (on Futurist typography)'
WHERE `slug` = 'futurisme';

UPDATE `courants` SET
  `description_longue` = 'Constructivism turned graphic design into an instrument of ideology. El Lissitzky, Rodchenko, and Varvara Stepanova built a visual language of red, black, and white — diagonal axes, photomontage, and sans-serif type stripped of ornament. The movement declared that design was not decoration but construction: every element on the page had a structural function. Constructivist posters and book covers for ROSTA propaganda windows showed how pure geometry and stark typographic hierarchy could produce images of tremendous visual force. Its modular grid, photomontage technique, and bold two-colour palette became foundational tools for 20th-century political and commercial graphic design.',
  `citation` = 'The new typography is functional, and in contrast to the old it serves communication and nothing else.',
  `citation_auteur` = 'El Lissitzky'
WHERE `slug` = 'constructivisme';

UPDATE `courants` SET
  `description_longue` = 'De Stijl reduced graphic design to its philosophical minimum: horizontal and vertical lines, primary colours plus black and white, no curves, no ornament. Theo van Doesburg\'s layout work for the De Stijl journal demonstrated that this extreme reduction was not impoverishment but purification — a grid of absolute clarity. Piet Zwart and César Domela applied the principles to commercial typography and advertising, producing compositions of striking visual tension from almost nothing. The movement\'s insistence on the right angle and primary colour palette became the bedrock of mid-century modernist layout and continues to influence grid-based design today.',
  `citation` = 'We must free art from all imitation of nature. We must eliminate all individuality, so that a universal plastic language can be created.',
  `citation_auteur` = 'Theo van Doesburg'
WHERE `slug` = 'de-stijl';

UPDATE `courants` SET
  `description_longue` = 'Bauhaus is the school that taught the 20th century how to design. Founded by Walter Gropius in Weimar in 1919, it unified fine art, craft, and industrial production under a single methodology: form follows function, the grid is the structure of thought, and sans-serif type is the honest voice of modernity. Herbert Bayer\'s universal typeface, László Moholy-Nagy\'s typophoto experiments, and the school\'s workshop-based pedagogy produced a graphic vocabulary — mathematical grid, primary colour, geometric icon, generous whitespace — that still defines corporate identity and interface design a century later. When the Nazis closed it in 1933, its diaspora carried modernism to America and the world.',
  `citation` = 'A beautifully designed object, a well-composed typeface — these are not luxuries. They are the grammar of a visual civilization.',
  `citation_auteur` = 'László Moholy-Nagy'
WHERE `slug` = 'bauhaus';

UPDATE `courants` SET
  `description_longue` = 'Art Deco was graphic design as declaration of luxury. A.M. Cassandre\'s monumental travel posters — the ocean liner reduced to pure geometric mass, the locomotive to speed lines — showed how strict symmetry and bold sans-serif lettering could produce images of overwhelming authority. The style fused the geometric rigour of Cubism with the materials of opulence: gold, lacquer, chrome, and the sharp shadow of streamlined form. From the covers of Vanity Fair to the signage of the Chrysler Building, Art Deco established a visual language of glamour and modernity that defined commercial graphic culture throughout the interwar period.',
  `citation` = 'A poster must arrest the passer-by in a single glance. It must make an immediate impression.',
  `citation_auteur` = 'A.M. Cassandre'
WHERE `slug` = 'art-deco';

UPDATE `courants` SET
  `description_longue` = 'Surrealism taught graphic design the power of the irrational. Designers who drew on Surrealist methods — unexpected scale relationships, objects displaced from their natural context, dreamlike juxtapositions — discovered that image combinations could bypass rational resistance and reach the viewer at a deeper level. Salvador Dalí\'s commercial work for Schiaparelli and his American advertising campaigns, Man Ray\'s fashion photography, and René Magritte\'s visual paradoxes showed that impossible images are more memorable than logical ones. The surrealist legacy is visible in every advertising campaign that creates unease, desire, or wonder by staging the impossible.',
  `citation` = 'The mind which plunges into Surrealism relives with glowing excitement the best part of its childhood.',
  `citation_auteur` = 'André Breton (Surrealist Manifesto, 1924)'
WHERE `slug` = 'surrealisme';

UPDATE `courants` SET
  `description_longue` = 'Swiss Style — also called the International Typographic Style — is the grammar of 20th-century visual communication. Developed in Zurich and Basel in the 1950s by Josef Müller-Brockmann, Armin Hofmann, and Emil Ruder, it systematised the principles Bauhaus had established: mathematical grid, flush-left ragged-right setting, Helvetica or Akzidenz Grotesk, photography over illustration, and generous whitespace as a structural element. Its poster and exhibition designs for Zurich\'s Tonhalle concert series demonstrated that rigorous system produced not coldness but clarity of extraordinary elegance. Swiss Style became the dominant visual language of multinational corporate identity and remains the default mode of serious typographic design.',
  `citation` = 'The grid system is an aid, not a guarantee. It permits a number of possible uses, and each designer can look for a solution appropriate to his personal style.',
  `citation_auteur` = 'Josef Müller-Brockmann'
WHERE `slug` = 'style-suisse';

UPDATE `courants` SET
  `description_longue` = 'Pop Art blew up the imagery of mass culture and handed it back to the public at monumental scale. Andy Warhol silk-screened Campbell\'s soup cans and Marilyn Monroe\'s face with the flat repetition of industrial printing; Roy Lichtenstein enlarged the Ben-Day dot of cheap comics to gallery proportions. For graphic design, Pop Art was a liberation: it validated bold colour, thick outline, humour, and the vernacular image of the street as legitimate design vocabulary. Its influence on magazine covers, record sleeves, and packaging design throughout the 1960s and 70s made commercial graphic culture as valid an artistic statement as any museum painting.',
  `citation` = 'I want to be a machine.',
  `citation_auteur` = 'Andy Warhol'
WHERE `slug` = 'pop-art';

UPDATE `courants` SET
  `description_longue` = 'The Push Pin Studio, founded in New York in 1954 by Milton Glaser and Seymour Chwast, proved that graphic design could be both historically literate and playfully irreverent. Where Swiss Style was rational and universal, Push Pin was eclectic and personal — borrowing from Art Nouveau, Victorian woodtype, medieval illumination, and comic strips with equal ease. Their illustration-heavy posters, record covers, and magazine spreads for New York magazine championed the idea that the designer\'s individual voice was a legitimate creative asset. This vernacular tradition opened the door to the expressive diversity that would define American graphic design for the next half-century.',
  `citation` = 'Design is the art of planning, and it is the art of making things possible.',
  `citation_auteur` = 'Milton Glaser'
WHERE `slug` = 'vernacular';

UPDATE `courants` SET
  `description_longue` = 'Psychedelic design transformed the concert poster into an act of visual rebellion. Wes Wilson, Victor Moscoso, and Rick Griffin pushed legibility to its limit — letterforms warped into waves and spirals, colours vibrated against each other at maximum saturation, Art Nouveau ornament returned in acid-tinged form. The Fillmore and Avalon Ballroom posters of 1966–68 were designed not to be read at a glance but to be studied, puzzled over, and collected. The movement showed that design could operate as an experience rather than a message, and its influence on record sleeve design, underground publishing, and festival graphics established a tradition of sensory overload as aesthetic strategy.',
  `citation` = 'The poster should make you stare at it trying to figure out what it says.',
  `citation_auteur` = 'Wes Wilson'
WHERE `slug` = 'psychedelique';

UPDATE `courants` SET
  `description_longue` = 'New Wave typography rebelled against the rational perfection of Swiss Style by doing exactly what Müller-Brockmann forbade: overlapping type, mixing typefaces on the same line, embracing distortion and deliberate imperfection. Wolfgang Weingart at the Basel School of Design was its intellectual provocateur — his students April Greiman and Dan Friedman carried his ideas to America, where they merged with California conceptualism and early digital experimentation. New Wave treated the printed page as a visual texture rather than a communication sequence, layering halftone screens, graduated tints, and fragmented letterforms into compositions of deliberate complexity. It was the typographic equivalent of punk: a refusal to be legible on the system\'s terms.',
  `citation` = 'What\'s the use of being legible, when nothing inspires you to take notice of it?',
  `citation_auteur` = 'Wolfgang Weingart'
WHERE `slug` = 'new-wave-typo';

UPDATE `courants` SET
  `description_longue` = 'Pixel art is the typography of hardware constraints. Born from the physical limitations of 8-bit and 16-bit screens — where every character was constructed from a grid of visible, square pixels — it developed its own aesthetic logic: dithering to simulate colour gradients, anti-aliasing replaced by deliberate aliasing, perspective achieved through isometric projection. Far from being a limitation overcome, the pixel grid became a genuine aesthetic choice: icon designers at Apple under Susan Kare, game artists like Mark Ferrari, and a generation of independent digital illustrators proved that the constrained palette and visible construction unit had an expressive power no smooth vector could match. Pixel art remains the defining visual language of gaming culture and digital nostalgia.',
  `citation` = 'Picasso had a saying: good artists borrow, great artists steal. And we have always been shameless about stealing great ideas.',
  `citation_auteur` = 'Steve Jobs (on the visual culture that made the Mac icon language possible)'
WHERE `slug` = 'pixel-art';

UPDATE `courants` SET
  `description_longue` = 'Postmodernism declared that there were no more rules — and then made a visual language out of breaking them all simultaneously. David Carson\'s layouts for Ray Gun magazine buried the text under layers of texture, rotated headlines 90 degrees, and chose illegibility as an aesthetic position. Paula Scher mixed historical typefaces with reckless audacity; Neville Brody deconstructed the magazine spread as an exercise in pure visual tension. Postmodern graphic design was ironic, self-referential, and deliberately excessive — it quoted history while refusing to respect it, layered collage over grid, and made the visible seams of construction part of the design itself. Its legacy is the permission it gave to express doubt, complexity, and contradiction through typography.',
  `citation` = 'I am in the gutter but looking at the stars. I\'m the space between the words.',
  `citation_auteur` = 'David Carson'
WHERE `slug` = 'postmodernisme';

UPDATE `courants` SET
  `description_longue` = 'Memphis exploded the functionalist consensus with maximum visual insolence. Founded by Ettore Sottsass in Milan in 1981, the group produced furniture, objects, and graphic patterns in which clashing colour, geometric pattern, and deliberate kitsch were not errors but arguments. For graphic design, Memphis introduced bold primary and pastel combinations, repeated geometric motifs — dots, squiggles, triangles — at high contrast, and a cheerful refusal of the minimalist aesthetic that had dominated design since the Swiss Style. Its influence spread rapidly to record sleeves, fashion advertising, and product packaging, injecting colour and playfulness into a design culture that had grown grey with seriousness.',
  `citation` = 'Memphis is a liberation from all the neurosis about good taste.',
  `citation_auteur` = 'Ettore Sottsass'
WHERE `slug` = 'memphis';

UPDATE `courants` SET
  `description_longue` = 'Grunge typography used the tools of production failure as deliberate aesthetic choices. David Carson at Beach Culture and Ray Gun, Barry Deck with Template Gothic, and Ed Fella\'s hand-lettered vernacular work all drew on photocopier degradation, worn typewriter ribbons, and the visual chaos of fanzine culture. The resulting typography looked damaged, layered, and willfully unreadable — each text an obstacle course rather than a delivery system. Grunge design captured the disaffection of early-90s alternative culture with perfect visual accuracy: it looked like things falling apart, which was precisely the point. Its influence on CD packaging, skateboard graphics, and alternative magazine design made it the signature visual style of Generation X.',
  `citation` = 'Never use a font you can read easily. If it\'s too readable, nobody bothers to look at it.',
  `citation_auteur` = 'David Carson (paraphrased)'
WHERE `slug` = 'grunge';

UPDATE `courants` SET
  `description_longue` = 'Y2K aesthetic was digital optimism made visible. As the millennium approached, graphic designers and interface artists reached for chrome, translucency, lens flare, and the cool sheen of computer-rendered surfaces to signal that the future had arrived. Designers Republic\'s work for Warp Records, Joshua Davis\'s generative web art, and the interface design of Windows XP and early iMac all drew on the same visual vocabulary: reflective metallics, liquid forms, glowing gradients, and a techno-optimism about the union of human and machine. The Y2K aesthetic is the graphic record of a cultural moment when technology felt genuinely new and the digital surface seemed to promise infinity.',
  `citation` = 'Technology is the campfire around which we tell our stories.',
  `citation_auteur` = 'Laurie Anderson'
WHERE `slug` = 'y2k';

UPDATE `courants` SET
  `description_longue` = 'Skeuomorphism made digital interfaces legible by making them look familiar. Jonathan Ive\'s early iOS designs and the interface culture of the 2000s used wood grain, leather stitching, green felt, and metal brushing to map the digital onto the physical world — reassuring users who were still learning to trust the touchscreen. For graphic design, this meant photorealistic rendering, detailed shadow and highlight, and materials whose simulated texture communicated function. A calendar looked like a desk calendar; a notepad looked like paper. The style was commercially brilliant and philosophically conservative, choosing comfort over truth, but it defined the visual grammar of consumer software for over a decade.',
  `citation` = 'Design is not just what it looks like and feels like. Design is how it works.',
  `citation_auteur` = 'Steve Jobs'
WHERE `slug` = 'skeuomorphisme';

UPDATE `courants` SET
  `description_longue` = 'Flat design stripped the interface of its physical pretensions and exposed the geometry underneath. Launched by Microsoft\'s Metro interface in 2010 and codified by Apple\'s iOS 7 redesign in 2013, it rejected the shadow, gradient, and material simulation of skeuomorphism in favour of bold solid colour, geometric iconography, clean sans-serif type, and the generosity of whitespace. The style was a visual argument: digital surfaces should look digital. Google\'s Material Design extended the approach into a systematic design language governing thousands of products. Flat design is the current default visual grammar of the digital world — so widespread it has become nearly invisible.',
  `citation` = 'Perfection is achieved not when there is nothing more to add, but when there is nothing left to take away.',
  `citation_auteur` = 'Antoine de Saint-Exupéry'
WHERE `slug` = 'flat-design';

UPDATE `courants` SET
  `description_longue` = 'Vaporwave is the graphic design of digital melancholy. Emerging from internet music communities around 2010, it built a visual world from the detritus of 1980s and 90s consumer culture: pastel neons, Roman marble busts rendered in pink and teal, glitched VHS textures, pixelated sunsets, and the corporate sans-serif typography of forgotten software packages. Its graphic language — assembled in tools like MS Paint and early Photoshop — treated nostalgia as a form of critique, aestheticising the empty promise of late-capitalist consumer culture. Vaporwave proved that internet micro-communities could generate genuine, original visual movements without institutions, galleries, or commercial sponsorship.',
  `citation` = 'The future used to be better.',
  `citation_auteur` = 'Vaporwave cultural motto (anonymous, internet origin)'
WHERE `slug` = 'vaporwave';

UPDATE `courants` SET
  `description_longue` = 'Web Brutalism is a design refusal. Where mainstream web design converges toward smooth gradients, card-based layouts, and system fonts optimised for readability, Brutalism insists on the raw infrastructure of HTML: black Times New Roman on white, underlined blue links, visible table borders, server-generated timestamps. Pascal Deville\'s Brutalist Websites archive and projects like Bloomberg Businessweek\'s editorial redesigns used the visual language of unformatted code to communicate authenticity, transparency, and resistance to corporate polish. Brutalist web design is less a style than a moral position: the refusal to hide the scaffolding, a trust in content over decoration.',
  `citation` = 'Good design is honest.',
  `citation_auteur` = 'Dieter Rams (10 Principles of Good Design)'
WHERE `slug` = 'brutalisme-web';

-- ── NIVEAU 2 — Sub-movements ─────────────────────────────────────

UPDATE `courants` SET
  `description_longue` = 'The Aesthetic Movement treated the visual page as a zone of pure pleasure, independent of moral or narrative purpose. Walter Crane\'s illustrated books and Kate Greenaway\'s children\'s annuals introduced delicate line illustration and refined flat colour printing into mass publishing. The movement\'s doctrine — art for art\'s sake — elevated the page border, the decorated initial, and the illustrated endpaper to objects of contemplative beauty, anticipating the design values of Art Nouveau.',
  `citation` = 'All art is quite useless.',
  `citation_auteur` = 'Oscar Wilde (The Picture of Dorian Gray, 1890)'
WHERE `slug` = 'aesthetic-movement';

UPDATE `courants` SET
  `description_longue` = 'Jugendstil was the German-language expression of Art Nouveau, shaped by the Münchner Jugend magazine from which it took its name. Its graphic designers — Peter Behrens, Otto Eckmann, and Julius Diez — developed a flatter, more geometrically disciplined version of the flowing organic style, combining ornamental letterforms with rigorous page architecture. Peter Behrens\'s later work for AEG, designing everything from logotype to factory buildings, made him the first designer to articulate a unified corporate visual identity — a concept that would define 20th-century brand design.',
  `citation` = 'The beautiful is not a luxury. It is a necessity.',
  `citation_auteur` = 'Peter Behrens'
WHERE `slug` = 'jugendstil';

UPDATE `courants` SET
  `description_longue` = 'Vorticism was Britain\'s most aggressive graphic intervention of the early 20th century. BLAST, the magazine Wyndham Lewis produced in 1914 with its stark black cover and diagonal typography shouting benedictions and curses in identical bold sans-serif blocks, was the most radical printed artefact of its era. The Vorticist page layout — hard geometric forms, maximum contrast, words treated as visual objects — anticipated both Constructivism and Dada\'s typographic experiments. For a movement that lasted barely two years, its influence on the aesthetics of English avant-garde printing was disproportionately large.',
  `citation` = 'Long live the great art vortex sprung up in the centre of this town!',
  `citation_auteur` = 'Wyndham Lewis (BLAST, 1914)'
WHERE `slug` = 'vorticism';

UPDATE `courants` SET
  `description_longue` = 'Neoplasticism — the pure abstraction of De Stijl reduced to its barest grid of lines and primaries — gave graphic design a systematic compositional vocabulary. Piet Mondrian\'s paintings were not designed for print, but their logic — divide the plane into rectangles, assign primary colour or white to each, articulate structure through line weight — translated directly into the page grid and advertising layout of the following decades. Every corporate annual report built on a ruled grid carries the ghost of Neoplasticist geometry.',
  `citation` = 'The position of the artist is humble. He is essentially a channel.',
  `citation_auteur` = 'Piet Mondrian'
WHERE `slug` = 'neoplasticisme';

UPDATE `courants` SET
  `description_longue` = 'The New Typography, codified by Jan Tschichold\'s 1928 manifesto Die Neue Typographie, was the theoretical foundation of 20th-century modern graphic design. Tschichold prescribed asymmetric layout, sans-serif type exclusively, functional use of white space, and the complete abolition of ornament — principles assembled from Bauhaus, Constructivism, and De Stijl into a practical manual for the commercial printer. His design for Penguin Books\' paperback covers in the 1940s showed that systematic constraint could produce visual culture of extraordinary consistency and quality. The New Typography remains the backbone of professional typographic education.',
  `citation` = 'The New Typography is distinguished from the old by the fact that its first objective is to develop its visible form out of the functions of the text.',
  `citation_auteur` = 'Jan Tschichold (Die Neue Typographie, 1928)'
WHERE `slug` = 'new-typography';

UPDATE `courants` SET
  `description_longue` = 'The Ulm School of Design, founded in West Germany in 1953 as the intellectual successor to the Bauhaus, pushed design methodology toward systematic rationalism. Otl Aicher and his colleagues developed a visual language of precision, ergonomic rigor, and modular grid systems that found its fullest expression in the 1972 Munich Olympics identity — a comprehensive programme of pictograms, colour coding, and typographic standards so complete that it defined international event identity design for decades. Aicher\'s pictogram system became the basis for every airport wayfinding programme, public signage system, and interface icon set that followed.',
  `citation` = 'The grid is the hidden structure of my work.',
  `citation_auteur` = 'Otl Aicher'
WHERE `slug` = 'ulm-school';

UPDATE `courants` SET
  `description_longue` = 'Streamline Moderne brought the aerodynamic language of speed to graphic design. The rounded form, horizontal speed lines, and chrome-effect letterforms that defined American industrial design in the 1930s translated directly into advertising typography and packaging design. Cassandre\'s iconic Normandie poster and the logotypes of American automobile brands applied the teardrop silhouette of wind-tunnel testing to two-dimensional graphic work, connecting product graphics to the era\'s faith in technology as salvation from the Depression.',
  `citation` = 'Speed is the form of ecstasy the technical revolution has bestowed on man.',
  `citation_auteur` = 'Milan Kundera (Slowness, 1995)'
WHERE `slug` = 'streamline';

UPDATE `courants` SET
  `description_longue` = 'Op Art brought optical illusion into the vocabulary of graphic design and surface pattern. Bridget Riley\'s undulating black-and-white compositions and Victor Vasarely\'s systematic colour progressions demonstrated that the eye could be made to perceive depth, movement, and vibration in a flat plane through the precise deployment of geometric repetition. For design, Op Art\'s contribution was the systematic use of pattern and contrast in fabric, packaging, poster, and environmental graphics — it showed that pure visual phenomenon, with no representational content, could hold a viewer\'s attention with extraordinary power.',
  `citation` = 'The aim of my work is to make the spectator fully aware of himself in the act of seeing.',
  `citation_auteur` = 'Bridget Riley'
WHERE `slug` = 'op-art';

UPDATE `courants` SET
  `description_longue` = 'Lettrisme, founded in Paris in 1945 by Isidore Isou, treated the letter as pure visual form stripped of linguistic meaning. Letterist poster poems and painted canvases decomposed text into its graphic components — the stroke, the counter, the terminal — and rearranged them as abstract pattern. While Lettrisme remained a marginal avant-garde movement, its insistence that typography is always simultaneously language and image influenced concrete poetry, Fluxus book design, and the generation of designers who would later treat type as texture rather than communication.',
  `citation` = 'The letter is not a vehicle of thought. It is thought itself, made visible.',
  `citation_auteur` = 'Isidore Isou'
WHERE `slug` = 'lettrisme';

UPDATE `courants` SET
  `description_longue` = 'The psychedelic concert poster was a genre that lasted barely six years but produced some of the most formally inventive graphic design of the 20th century. Created at industrial speed for Fillmore and Avalon Ballroom events in San Francisco between 1966 and 1972, these posters by Wes Wilson, Victor Moscoso, and Rick Griffin deliberately pushed letterforms to the edge of illegibility — swollen, liquefied, vibrating against saturated complementary backgrounds. The genre proved that graphic design could function as a purely perceptual experience, prioritising sensation over information, and its influence on concert poster design and alternative publishing persists to this day.',
  `citation` = 'If you can read it, it isn\'t psychedelic enough.',
  `citation_auteur` = 'Victor Moscoso (attributed)'
WHERE `slug` = 'psychedelic-poster';

UPDATE `courants` SET
  `description_longue` = 'Typographic Deconstruction, rooted in the literary theory of Derrida and practised most visibly by the Cranbrook Academy designers in the late 1980s and 90s, treated the printed page as a site of unstable meaning rather than clear communication. Katherine McCoy and her students scattered type across the page, broke words mid-syllable, layered transparent text over image, and exposed the arbitrary nature of typographic convention. The work asked whether a designed page communicated or merely performed communication — a question that remains relevant in the age of algorithmically generated content.',
  `citation` = 'There is nothing outside the text.',
  `citation_auteur` = 'Jacques Derrida (Of Grammatology, 1967)'
WHERE `slug` = 'deconstruction-typo';

UPDATE `courants` SET
  `description_longue` = 'Neo-Pop brought Pop Art\'s vocabulary of celebrity, commodity, and irony into the 1980s and 90s with updated media literacy. Jeff Koons\'s slick advertising aesthetics, Keith Haring\'s subway chalk figures reproduced on T-shirts and gallery walls, and the appropriation art of Richard Prince all drew on Pop\'s equation of the commercial and the fine art image — but now with a sharper awareness of the image as recyclable data. For graphic design, Neo-Pop validated the use of product photography, brand graphics, and advertising clichés as design material, a principle that fed directly into the knowing visual culture of 90s magazine design and contemporary brand communication.',
  `citation` = 'I am for an art that is political-erotica-mystical, that does something other than sit on its ass in a museum.',
  `citation_auteur` = 'Claes Oldenburg (I Am For An Art, 1961)'
WHERE `slug` = 'neo-pop';

UPDATE `courants` SET
  `description_longue` = 'Material Design, published by Google in 2014, is the most influential systematic design language of the digital era. Designed by Matias Duarte and his team, it reintroduced a limited, deliberate version of depth and shadow — the "material" metaphor — to flat interfaces, giving digital surfaces a physical logic of layering and elevation without returning to the literal textures of skeuomorphism. Its comprehensive documentation, covering everything from touch feedback timings to typographic scale ratios, created a shared visual grammar for thousands of Android applications and established the design system as the primary deliverable of contemporary digital product design.',
  `citation` = 'Material is the metaphor.',
  `citation_auteur` = 'Google Design (Material Design introduction, 2014)'
WHERE `slug` = 'material-design';

UPDATE `courants` SET
  `description_longue` = 'Synthwave revived the graphic aesthetic of 1980s science fiction and electronic music with the production tools of the 2010s. Its visual language — neon grid horizons receding to a vanishing point, chrome letterforms, chrome-effect gradients in pink and electric blue, VHS scanlines — assembled references from Tron, Blade Runner, and Miami Vice into a coherent retro-futurist style. For digital graphic design, Synthwave validated the use of aggressive gradient, neon glow effects, and geometric perspective grids as a legitimate contemporary aesthetic, proving that nostalgia rendered in high resolution could feel genuinely new.',
  `citation` = 'The 1980s will always be the future.',
  `citation_auteur` = 'Synthwave cultural axiom (anonymous, internet origin)'
WHERE `slug` = 'synthwave';

UPDATE `courants` SET
  `description_longue` = 'Lo-fi aesthetic is the visual culture of deliberate imperfection. Emerging from independent music communities and internet culture in the mid-2010s, its graphic language draws on grain, film burn, cassette distortion, and the muted colour palette of faded analogue photography. For graphic design, lo-fi represents a reaction against the hyper-polished perfectionism of commercial digital work — a deliberate embrace of texture, noise, and visual fatigue as signs of authenticity. Its influence on album artwork, editorial illustration, and brand identity for independent businesses has made warm grain and desaturated tone the defining aesthetic of millennial cultural self-expression.',
  `citation` = 'The noise is part of the music.',
  `citation_auteur` = 'Toro y Moi (on lo-fi aesthetic, 2019)'
WHERE `slug` = 'lo-fi-aesthetic';
