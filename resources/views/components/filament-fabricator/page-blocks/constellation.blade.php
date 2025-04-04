@aware(['page'])

<div class="bg-sky-800 fixed -z-50">
    <canvas></canvas>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/zepto/1.2.0/zepto.min.js"
    integrity="sha512-BrvVYNhKh6yST24E5DY/LopLO5d+8KYmIXyrpBIJ2PK+CyyJw/cLSG/BfJomWLC1IblNrmiJWGlrGueKLd/Ekw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    /*!
     * Mantis.js / jQuery / Zepto.js plugin for Constellation
     * @version 1.2.2
     * @author Acauã Montiel <contato@acauamontiel.com.br>
     * @license http://acaua.mit-license.org/
     */
    (($, window) => {
        const $window = $(window);
        /**
         * Makes a nice constellation on canvas
         * @constructor Constellation
         */
        function Constellation(canvas, options) {
            const $canvas = $(canvas),
                context = canvas.getContext('2d'),
                defaults = {
                    star: {
                        color: 'rgba(255, 255, 255, .5)',
                        width: 1,
                        randomWidth: true
                    },
                    line: {
                        color: 'rgba(255, 255, 255, .5)',
                        width: 0.2
                    },
                    position: {
                        x: 0,
                        y: 0
                    },
                    width: window.innerWidth,
                    height: window.innerHeight,
                    velocity: 0.1,
                    length: 100,
                    distance: 120,
                    radius: 150,
                    stars: []
                },
                config = $.extend(true, {}, defaults, options);

            function Star() {
                this.x = Math.random() * canvas.width;
                this.y = Math.random() * canvas.height;

                this.vx = (config.velocity - (Math.random() * 0.5));
                this.vy = (config.velocity - (Math.random() * 0.5));

                this.radius = config.star.randomWidth ? (Math.random() * config.star.width) : config.star.width;
            }

            Star.prototype = {
                create() {
                    context.beginPath();
                    context.arc(this.x, this.y, this.radius, 0, Math.PI * 2, false);
                    context.fill();
                },

                animate() {
                    let i;
                    for (i = 0; i < config.length; i++) {

                        const star = config.stars[i];

                        if (star.y < 0 || star.y > canvas.height) {
                            star.vx = star.vx;
                            star.vy = -star.vy;
                        } else if (star.x < 0 || star.x > canvas.width) {
                            star.vx = -star.vx;
                            star.vy = star.vy;
                        }

                        star.x += star.vx;
                        star.y += star.vy;
                    }
                },

                line() {
                    const length = config.length;
                    let iStar;
                    let jStar;
                    let i;
                    let j;

                    for (i = 0; i < length; i++) {
                        for (j = 0; j < length; j++) {
                            iStar = config.stars[i];
                            jStar = config.stars[j];

                            if (
                                (iStar.x - jStar.x) < config.distance &&
                                (iStar.y - jStar.y) < config.distance &&
                                (iStar.x - jStar.x) > -config.distance &&
                                (iStar.y - jStar.y) > -config.distance
                            ) {
                                if (
                                    (iStar.x - config.position.x) < config.radius &&
                                    (iStar.y - config.position.y) < config.radius &&
                                    (iStar.x - config.position.x) > -config.radius &&
                                    (iStar.y - config.position.y) > -config.radius
                                ) {
                                    context.beginPath();
                                    context.moveTo(iStar.x, iStar.y);
                                    context.lineTo(jStar.x, jStar.y);
                                    context.stroke();
                                    context.closePath();
                                }
                            }
                        }
                    }
                }
            };

            this.createStars = () => {
                const length = config.length;
                let star;
                let i;

                context.clearRect(0, 0, canvas.width, canvas.height);

                for (i = 0; i < length; i++) {
                    config.stars.push(new Star());
                    star = config.stars[i];

                    star.create();
                }

                star.line();
                star.animate();
            };

            this.setCanvas = () => {
                canvas.width = config.width;
                canvas.height = config.height;
            };

            this.setContext = () => {
                context.fillStyle = config.star.color;
                context.strokeStyle = config.line.color;
                context.lineWidth = config.line.width;
            };

            this.setInitialPosition = () => {
                if (!options || !options.hasOwnProperty('position')) {
                    config.position = {
                        x: canvas.width * 0.5,
                        y: canvas.height * 0.5
                    };
                }
            };

            this.loop = function(callback) {
                callback();

                this.rAF = window.requestAnimationFrame(() => {
                    this.loop(callback);
                });
            };

            this.handlers = {
                window: {
                    mousemove(e) {
                        config.position.x = e.pageX - $canvas.offset().left;
                        config.position.y = e.pageY - $canvas.offset().top;
                    },
                    resize() {
                        window.cancelAnimationFrame(this.rAF);
                    }
                }
            };

            this.bind = function() {
                $window
                    .on('mousemove', this.handlers.window.mousemove)
                    .on('resize', this.handlers.window.resize.bind(this));
            };

            this.unbind = function() {
                $window
                    .off('mousemove', this.handlers.window.mousemove)
                    .off('resize', this.handlers.window.resize);
            }

            this.init = function() {
                this.setCanvas();
                this.setContext();
                this.setInitialPosition();
                this.loop(this.createStars);
                this.bind();
            };
        }

        function instantiate(element, options) {
            const c = new Constellation(element, options);
            c.init();
        }

        $.fn.constellation = function(options) {
            return this.each(function() {
                $window.on('resize', () => {
                    instantiate(this, options);
                });

                instantiate(this, options);
            });
        };
    })($, window);

    // Init plugin
    $('canvas').constellation({
        star: {
            width: 3
        },
        line: {
            color: 'rgba(255, 255, 255, .5)'
        },
        length: (window.innerWidth / 6),
        radius: (window.innerWidth / 5)
    });
</script>
