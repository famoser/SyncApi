using System;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Repositories
{
    public partial class ApiRepository<TModel>
    where TModel : ISyncModel
    {
        private string _modelCacheFilePath;
        private string GetModelCacheFilePath()
        {
            if (_modelCacheFilePath == null)
                return _modelCacheFilePath;

            var model = (TModel)Activator.CreateInstance(typeof(TModel));
            _modelCacheFilePath = _apiConfigurationService.GetFileName(model.GetUniqeIdentifier() + ".json", typeof(TModel));

            return _modelCacheFilePath;
        }
    }
}
