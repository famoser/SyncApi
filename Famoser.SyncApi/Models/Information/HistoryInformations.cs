using System;
using Famoser.SyncApi.Models.Information.Base;
using Newtonsoft.Json;

namespace Famoser.SyncApi.Models.Information
{
    public class HistoryInformations<TModel> : CacheInformations
    {
        [JsonIgnore]
        public TModel Model { get; set; }
    }
}
