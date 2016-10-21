using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Base;
using Famoser.SyncApi.Models.Interfaces;

namespace Famoser.SyncApi.Models
{
    public class CollectionModel : BaseModel, ICollectionModel
    {
        public override string GetUniqeIdentifier()
        {
            return "collection";
        }
    }
}
