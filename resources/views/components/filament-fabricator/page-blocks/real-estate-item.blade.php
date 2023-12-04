@aware(['page'])
@foreach ($rows->chunk(3) as $rows)
    <div class="mb-4 space-x-2  lg:flex lg:flex-nowrap lg:space-x-4 lg:mt-0  justify-center">
        @foreach ($rows as $row)
            <div class="p-4 bg-white rounded-lg">
                
                    <x-curator-glider class="w-auto h-64 rounded-lg" :media="$row->featuredImage" />

                <div class="p-6">
                    <div class="flex justify-center items-center">
                        <div class="mt-2">
                            <h4 class="text-2xl font-bold cursor-pointer">{{ $row->name }} </h4>
                        </div>
                    </div>
                    <div class="flex justify-center items-center">
                        <div class="mt-2">
                            <h2 class="text-xs text-indigo-500 tracking-widest font-medium title-font text-center mb-1">
                                PREZZO</h2>
                            <span class="text-xl font-extrabold text-blue-600 text-center">€{{ $row->price }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between p-4 text-gray-700 border-t border-gray-300 mt-4">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" height="16" width="16"
                                viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2023 Fonticons, Inc.-->
                                <path
                                    d="M.2 468.9C2.7 493.1 23.1 512 48 512l96 0 320 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-48 0 0 80c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-80-64 0 0 80c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-80-64 0 0 80c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-80-80 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l80 0 0-64-80 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l80 0 0-64-80 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l80 0 0-48c0-26.5-21.5-48-48-48L48 0C21.5 0 0 21.5 0 48L0 368l0 96c0 1.7 .1 3.3 .2 4.9z" />
                            </svg>

                            <p><span class="font-bold text-gray-900">3</span> Mq</p>
                        </div>
                        <div class="flex items-center">
                            <svg version="1.1" id="Icons" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 32 32" xml:space="preserve"
                                width="16" height="16" fill="#000000" transform="rotate(0)">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <style type="text/css">
                                        .st0 {
                                            fill: none;
                                            stroke: #000000;
                                            stroke-width: 2;
                                            stroke-linecap: round;
                                            stroke-linejoin: round;
                                            stroke-miterlimit: 10;
                                        }

                                        .st1 {
                                            fill: none;
                                            stroke: #000000;
                                            stroke-width: 2;
                                            stroke-linejoin: round;
                                            stroke-miterlimit: 10;
                                        }

                                        .st2 {
                                            fill: none;
                                            stroke: #000000;
                                            stroke-width: 2;
                                            stroke-linecap: round;
                                            stroke-miterlimit: 10;
                                        }
                                    </style>
                                    <rect x="3" y="3" class="st0" width="26" height="26"></rect>
                                    <rect x="18" y="20" class="st0" width="11" height="9"></rect>
                                    <polyline class="st0" points="14,3 29,3 29,15 20,15 "></polyline>
                                    <line class="st0" x1="14" y1="15" x2="14" y2="3">
                                    </line>
                                    <rect x="18" y="23" class="st0" width="11" height="6"></rect>
                                    <rect x="18" y="26" class="st0" width="11" height="3"></rect>
                                    <polyline class="st0" points="11,29 11,20 7,20 "></polyline>
                                    <polyline class="st0" points="14,15 3,15 3,3 14,3 "></polyline>
                                </g>
                            </svg>
                            <p><span class="font-bold text-gray-900">2</span> LOCALI</p>
                            <div class="flex justify-center items-center">
                            </div>

                        </div>

                    </div>
                    <div class="flex justify-between p-4 text-gray-700 border-b border-gray-300">
                        <div class="flex items-center">
                            <svg width="16" height="16" viewBox="0 0 20 20" version="1.1"
                                xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                fill="#000000">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <title>stairs [#56]</title>
                                    <desc>Created with Sketch.</desc>
                                    <defs> </defs>
                                    <g id="Page-1" stroke="none" stroke-width="1" fill="none"
                                        fill-rule="evenodd">
                                        <g id="Dribbble-Light-Preview" transform="translate(-140.000000, -7959.000000)"
                                            fill="#000000">
                                            <g id="icons" transform="translate(56.000000, 160.000000)">
                                                <path
                                                    d="M102,7817 L86,7817 L86,7801 L90,7801 L90,7807 L96,7807 L96,7813 L102,7813 L102,7817 Z M98,7811 L98,7805 L92,7805 L92,7799 L84,7799 L84,7819 L104,7819 L104,7811 L98,7811 Z"
                                                    id="stairs-[#56]"> </path>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </svg>
                            <p><span class="font-bold text-gray-900">3</span> PIANO</p>
                        </div>
                        <div class="flex items-center">
                            <svg width="16" height="16" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg"
                                fill="#000000">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path fill="var(--ci-primary-color, #000000)"
                                        d="M464,280H80V100A51.258,51.258,0,0,1,95.113,63.515l.4-.4a51.691,51.691,0,0,1,58.6-10.162,79.1,79.1,0,0,0,11.778,96.627l10.951,10.951-20.157,20.158,22.626,22.626,20.157-20.157h0L311.157,71.471h0l20.157-20.157L308.687,28.687,288.529,48.844,277.578,37.893a79.086,79.086,0,0,0-100.929-8.976A83.61,83.61,0,0,0,72.887,40.485l-.4.4A83.054,83.054,0,0,0,48,100V280H16v32H48v30.7a23.95,23.95,0,0,0,1.232,7.589L79,439.589A23.969,23.969,0,0,0,101.766,456h12.9L103,496h33.333L148,456H356.1l12,40H401.5l-12-40h20.73A23.969,23.969,0,0,0,433,439.589l29.766-89.3A23.982,23.982,0,0,0,464,342.7V312h32V280ZM188.52,60.52a47.025,47.025,0,0,1,66.431,0L265.9,71.471,199.471,137.9,188.52,126.951A47.027,47.027,0,0,1,188.52,60.52ZM432,341.4,404.468,424H107.532L80,341.4V312H432Z"
                                        class="ci-primary"></path>
                                </g>
                            </svg>
                            <p><span class="font-bold text-gray-900">2</span> BAGNI</p>
                            <div class="flex justify-center items-center">
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach
