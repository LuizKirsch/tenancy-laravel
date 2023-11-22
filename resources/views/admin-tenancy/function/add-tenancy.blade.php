<x-layout>
    <form class="max-w-sm mx-auto" method="POST" action="{{Route('create_action')}}">
      @csrf
        <div class="mb-5">
          <label for="domain" class="block mb-2 text-sm font-medium text-red-900 dark:text-black">Dominio</label>
          <input type="text" id="domain" name="domain" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-red-700 dark:border-red-600 dark:placeholder-red-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="name@flowbite.com" required>
        </div>
        {{-- <label for="data" class="block mb-2 text-sm font-medium text-red-900 dark:text-black">Data</label>
        <textarea id="data" name="data" rows="4" class="block p-2.5 w-full text-sm text-red-900 bg-red-50 rounded-lg border border-red-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-red-700 dark:border-red-600 dark:placeholder-red-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Leave a comment..."></textarea> --}}
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
      </form>
</x-layout>   