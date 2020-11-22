<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Parser\CommLink;

use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\Category\Category;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use App\Models\Rsi\CommLink\Series\Series;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Class ParseCommLinkTest
 */
class ParseCommLinkTest extends TestCase
{
    private $commLink = <<<EOL
<html>
<head>
    <meta charset="utf-8">
    <script>
        window.Mark = new Turbulent.Mark({'name': 'Rsi-XSRF', 'token': 'QHqvWw:TkX3N6IX6ez+XllC5qc7sg', 'ttl': 1800})
    </script>
    <title>
        Welcome to the Comm-Link! - Roberts Space Industries | Follow the development of Star Citizen and Squadron 42
    </title>
</head>
<body id="" class="">
    <div id="bodyWrapper">
        <div class="page-wrapper">
            <div id="post-background"></div>
            <div id="contentbody" class="" style="">
                <div id="post">
                    <div class="title-section">
                        <div class="wrapper title-bar-container">
                            <div class="glow left"></div>
                            <div class="title-bar">
                                <div class="title with-subtitle">
                                    <h1>Transmission</h1>
                                    <h2>General</h2>
                                </div>
                                <div class="details">
                                    <div>
                                        <h3>ID:</h3>
                                        <p>12663</p>
                                        <div class="cboth"></div>
                                    </div>
                                    <div>
                                        <h3>Comments:</h3>
                                        <p class="comment-count">130</p>
                                        <div class="cboth"></div>
                                    </div>
                                    <div>
                                        <h3>Date:</h3>
                                        <p>September 5th 2012</p>
                                        <div class="cboth"></div>
                                    </div>
                                    <div></div>
                                </div>
                                <div class="cboth"></div>
                            </div>
                            <div class="glow right"></div>
                        </div>
                        <div class="title-container">
                            <div class="title no-subtitle">Welcome to the Comm-Link!</div>
                        </div>
                    </div>
    
    
                    <div class="lightbar toplightbar">
                        <div class="lightbarlights"></div>
                        <div class="footershadow"></div>
                    </div>
    
    
                    <div class="wrapper">
                        <div class="hr"></div>
                        <div class="content-block4">
                            <div class="content">
                                <div class="top"></div>
                                <h1>Welcome to the Comm-Link!</h1>
                                <div class="cboth"></div>
                            </div>
                            <div class="bottom"></div>
                        </div>
                        <div class="content-block2">
                            <div class="content clearfix">
                                <img src="/media/bluo97w6u7n1ur/post_section_header/Starshipbridge.jpg">
                                <div class="corner corner-top-left"></div>
                                <div class="corner corner-top-right"></div>
                                <div class="corner corner-bottom-left"></div>
                                <div class="corner corner-bottom-right"></div>
                            </div>
                        </div>
                        <div class="content-block1 rsi-markup">
                            <div class=" segment">
                                <div class="content">
                                    <p><span class="initial">C</span>ongratulations on successfully navigating the jump to
                                        Roberts Space Industries!</p>
                                    <h3><strong>Author: <span class="caps">GEN</span> Neal Socolovich <sup><a href="#note9699554165baf7bd683ce9-2"><span id="noteref9699554165baf7bd683ce9-1">1</span></a></sup></strong></h3>
                                    <iframe width="640" height="360" src="//www.youtube.com/embed/2xEPygBUoqA?wmode=transparent" frameborder="0" allowfullscreen=""></iframe>
                                    <p>The Comm-Link is the primary communication channel from all of us at <span
                                            class="caps">RSI</span>. As you can tell from the ticking counter, there’s going
                                        to be an announcement in October about our newest research and development project.
                                        We hope you’ll be as excited about it as we are and choose to sign up for the first
                                        models off the assembly line.</p>
    
                                    <p>Until then, the goal of the Roberts Space Industries website is to bring together
                                        truly hardcore space simulation fans from around the world to celebrate the genre
                                        and remind them what made Wing Commander, Privateer, Starlancer and Freelancer
                                        great!</p>
    
                                    <p>It will offer material from our Chairman’s previous creations: it will tell you about
                                        their history, spotlight the fans who have been keeping the faith over the years,
                                        let you interact with the <span class="caps">RSI</span> team.</p>
    
                                    <p>It will also give a few hints as to what is coming.</p>
    
                                    <p>We know you’re here because you’re one of the most dedicated fans on the planet… so
                                        settle in, make the <a href="http://robertsspaceindustries.com/forums/">forums</a> your
                                        home and get ready for the big announcement! In the meantime, check back each
                                        morning for a new Comm-Link update.</p>
                                    <a class="image  js-open-in-slideshow" data-id="ob7xg2o5gn2ber" data-full_res="/media/ob7xg2o5gn2ber/source/Poll_4.png" data-low_res="/media/ob7xg2o5gn2ber/slideshow_pager/Poll_4.png" data-source_url="/media/ob7xg2o5gn2ber/source/Poll_4.png" rel="post"><img src="/media/ob7xg2o5gn2ber/post/Poll_4.png" alt=""></a>
                                </div>
                                <div class="cboth"></div>
                            </div>
                            <div class="top-line-thin"></div>
                            <div class="top-line"></div>
                            <div class="corner corner-top-left"></div>
                            <div class="corner corner-top-right"></div>
                            <div class="corner corner-bottom-left"></div>
                            <div class="corner corner-bottom-right"></div>
                        </div>
                    </div>
    
    
                    <div class="wrapper">
    
                        <div class="end-transmission-container">
                            <div class="glow left"></div>
                            <div class="title-bar">
                                <h1>End Transmission</h1>
                            </div>
                            <div class="glow right"></div>
                        </div>
                    </div>
    
    
                    <div class="two-line-separator"></div>
    
    
                    <div class="wrapper force-one-column" id="comments">
                        <div class="holder">
                            <div class="title-bar">
                                <div class="count">
                                    <div class="label">Comments</div>
                                    <div class="value">0130.<span class="js-comment-decimal">0</span>
                                    </div>
                                    <div class="cboth"></div>
                                </div>
                                <div class="title">
                                    <h1>Feedback</h1>
                                </div>
                                <div class="cboth"></div>
                            </div>
    
                            <div class="main-buttons-holder-container">
                                <div class="main-buttons-holder">
                                    <a href="/connect?jumpto=/comm-link/transmission/12663-Welcome-To-The-Comm-Link"
                                       class="add-comment js-add-comment trans-02s js-modal-login">Add New Comment</a>
                                    <div class="settings js-settings">
                                        <h1>Settings</h1>
                                        <label>View mode:</label>
                                        <a href="#" class="js-comments-view to-one-column js-to-one-column trans-01s on">One
                                            column</a>
                                        <a href="#" class="js-comments-view to-two-columns js-to-two-columns trans-01s ">Two
                                            columns</a>
                                        <label>Sort by:</label>
                                        <a href="#" class="js-comments-sort to-oldest js-to-oldest trans-01s on">Oldest
                                            first</a>
                                        <a href="#" class="js-comments-sort to-newest js-to-newest trans-01s ">Newest
                                            first</a>
                                        <a href="#" class="js-comments-sort to-appreciated js-to-appreciated trans-01s ">Most
                                            appreciated first</a>
    
                                    </div>
                                    <a href="" class="view-settings js-view-settings">
                                        <div class="hover trans-02s"></div>
                                    </a>
                                    <div class="triangle"></div>
                                </div>
                            </div>
    
    
                            <div class="comment-listing"></div>
    
                            <div class="comment-loader traj-loader">
                                <div class="fast-blink"></div>
                                <span>Loading Additional Feedback</span>
                            </div>
    
                            <div class="cboth"></div>
    
                        </div>
                    </div>
    
                    <script type="text/javascript">
                      $(document).ready(function () {
                        window.comments = new RSI.Comments({
                          atom_url: '/comm-link/transmission/12663-Welcome-To-The-Comm-Link',
                          comment_listing_el: $('.comment-listing'),
                          comment_submission_form_el: $('.comment-submission'),
                          api_object: RSI.Api.Comments,
                          lazyLoading: true,
                          comment_count: 130,
                          subject_id: 12663,
                          append: false,
                          texts: {
                            'edit': {
                              'cancel': 'Cancel',
                              'submit': 'Save Changes'
                            },
                            'reply': {
                              'cancel': 'Cancel',
                              'submit': 'Save Changes'
                            }
                          },
                          'insertion_conf': {
                            'mode': 'html'
                          },
                          'api_function_names': {
                            'delete': 'erase',
                            'load': 'listing',
                            'sort': 'listing'
                          }
                        })
                      })
                    </script>
    
                </div>
            </div>
        </div>
    </div>
</body>
</html>
EOL;

    /**
     * @covers \App\Jobs\Rsi\CommLink\Import\ImportCommLink
     * @covers \App\Services\Parser\CommLink\AbstractBaseElement
     * @covers \App\Services\Parser\CommLink\Content
     * @covers \App\Services\Parser\CommLink\Image
     * @covers \App\Services\Parser\CommLink\Link
     * @covers \App\Services\Parser\CommLink\Metadata
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function testParsing()
    {
        $parser = new ImportCommLink(1, '1.html');
        Storage::disk('comm_links')->put('1/1.html', $this->commLink);

        $parser->handle();

        $this->assertDatabaseHas(
            'comm_links',
            [
                'title' => 'Welcome to the Comm-Link!',
            ]
        );

        $this->assertDatabaseHas(
            'comm_link_links',
            [
                'text' => 'iframe',
            ]
        );

        self::assertCount(1, CommLink::all());
        self::assertCount(2, Image::all());
        self::assertCount(2, Link::all());

        Storage::disk('comm_links')->delete('1');
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Category::factory()->create();
        Series::factory()->create();
        Channel::factory()->create();
    }
}
