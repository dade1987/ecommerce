@aware(['page'])

<div class="w-screen text-center fixed z-0 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-white bg-opacity-50 p-8 w-1/2">

        <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Quote Generator</h2>
        <form action="#">
            <div class="grid gap-4 mb-4 sm:grid-cols-2 sm:gap-6 sm:mb-5">
                <div class="sm:col-span-2">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Project
                        Name</label>
                    <input type="text" name="name" id="name"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        value="Example: Dr. Smith's Dental Clinic Showcase Website" placeholder="Type project name"
                        required="">
                </div>
                
                <div class="sm:col-span-2">
                    <label for="description"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Description</label>
                    <textarea id="description" rows="8"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Write the most accurate description possible of your project...">Example: build a professional website for Dr. Smith’s Dental Clinic to enhance its online presence. The site will feature a clean, responsive design with detailed service descriptions, online booking, and a patient testimonial section. The goal is to attract more patients and showcase the clinic's expertise.
                     </textarea>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit"
                    class="focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-white bg-gray-900 bg-opacity-20 border border-gray-300 focus:outline-none hover:bg-gray-900 hover:bg-opacity-40">
                    Calculate the Quote
                </button>
            </div>
        </form>
    </div>
