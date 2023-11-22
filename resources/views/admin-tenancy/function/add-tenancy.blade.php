<x-layout>
    <form class="max-w-sm mx-auto" method="POST" action="{{Route('create_action')}}">
      @csrf
        <div class="mb-5">
          <label for="domain" class="block mb-2 text-sm font-medium text-red-900 dark:text-black">Dominio</label>
          <input type="text" id="domain" name="domain" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-red-700 dark:border-red-600 dark:placeholder-red-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Ex: loja-principal" required>
        </div>
        <div class="mb-5">
          <label for="countries" class="block mb-2 text-sm font-medium text-red-900 dark:text-black">Select an option</label>
          <select id="countries" name="plan" class="bg-red-50 border border-red-300 text-red-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-red-700 dark:border-red-600 dark:placeholder-red-400 dark:text-black dark:focus:ring-blue-500 dark:focus:border-blue-500">
              <option selected>Selecionar Plano</option>
              <option value="free">Free</option>
              <option value="standard">Standard</option>
              <option value="premium">Premium</option>
          </select>
          </select>
        </div>
        
        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Submit</button>
      </form>
</x-layout>   