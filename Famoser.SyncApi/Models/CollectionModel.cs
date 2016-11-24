using Famoser.SyncApi.Models.Base;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class CollectionModel : BaseModel, ICollectionModel
    {
        public override string GetClassIdentifier()
        {
            return "collection";
        }
    }
}
