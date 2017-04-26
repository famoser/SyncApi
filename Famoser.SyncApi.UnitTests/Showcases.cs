using System.Collections.ObjectModel;
using System.Threading.Tasks;
using Famoser.FrameworkEssentials.Services.Interfaces;
using Famoser.SyncApi.Helpers;
using Famoser.SyncApi.UnitTests.Implementations;
using Famoser.SyncApi.UnitTests.Models;

namespace Famoser.SyncApi.UnitTests
{
    class ShowCases
    {
        /// <summary>
        /// Usecase: I want to save a NoteModel presistently, and want to be able to retrieve it on all the devices of the user.
        /// </summary>
        /// <returns></returns>
        public static async Task SimpleUseCase()
        {
            //construct the api helper (storage service is implementated in Famoser.UniversalEssentials for UWP)
            IStorageService ss = new StorageService();
            var helper = new SyncApiHelper(ss, "my_application_name", "https://api.mywebpage.ch");

            //get my repository
            var repo = helper.ResolveRepository<NoteModel>();

            //save my model
            await repo.SaveAsync(new NoteModel { Content = "Hallo Welt!" });

            //retrieve it later on
            ObservableCollection<NoteModel> coll = await repo.GetAllAsync();
        }
    }
}
